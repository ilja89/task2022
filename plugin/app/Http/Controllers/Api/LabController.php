<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;
use Zeizig\Moodle\Models\Course;

class LabController extends Controller
{
    /** @var LabRepository */
    private $labRepository;

    /**
     * LabDummyController constructor.
     *
     * @param Request $request
     * @param LabRepository $labRepository
     */
    public function __construct(Request $request, LabRepository $labRepository)
    {
        parent::__construct($request);
        $this->labRepository = $labRepository;
    }

    /**
     * Get Labs by course.
     *
     * @param Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lab[]
     */
    public function getByCourse(Course $course)
    {
        return $this->labRepository->findLabsByCourse($course->id);
    }

    /**
     * Get all Labs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lab[]
     */
    public function all()
    {
        return $this->labRepository->getAllLabs();
    }

    /**
     * Save lab.
     * @param Course $course
     *
     * @return bool
     */
    public function save(Course $course)
    {
        return $this->labRepository->save(
            $this->request['start'],
            $this->request['end'],
            $this->request['name'],
            $course->id,
            $this->request['teachers'],
            $this->request['charons'],
            $this->request['groups'],
            $this->request['weeks']
        );
    }

    /**
     * Update lab.
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     */
    public function update(Course $course, Lab $lab)
    {
        return $this->labRepository->update(
            $lab->id,
            $this->request['start'],
            $this->request['end'],
            $this->request['name'],
            $this->request['teachers'],
            $this->request['charons'],
            $this->request['groups']
        );
    }

    /**
     * Delete lab.
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     */
    public function delete(Course $course, Lab $lab)
    {
        return $this->labRepository->deleteByInstanceId($lab->id);
    }

    /**
     * @param Course $course
     * @return mixed
     */
    public function getCourse(Course $course)
    {
        return $this->labRepository->getCourse($course->id);
    }

    /**
     * @param Charon $charon
     * @return Lab[]
     */
    public function getByCharon(Charon $charon)
    {
        return $this->labRepository->getLabsByCharonId($charon->id);
    }

    /**
     * Gets all groups and groupings for course
     *
     * @param int $courseId         The course identifier
     * @return []                   Array containing arrays of groups and groupings
     */
    public function getGroups(int $courseId)
    {
        $groups = $this->labRepository->getAllGroups($courseId);
        $groupings = $this->labRepository->getAllGroupings($courseId);

        //collect info about groups together, into single grouping object
        $groupObjects = array_column($groups->toArray(), null, "id");
        $result = [];
        foreach ($groupings as $g)
        {
            $id = $g['id'];
            $groupid = $g['groupid'];
            $group = $groupObjects[$groupid];
            if (array_key_exists($id, $result)) {
                array_push($result[$id]['groups'], $group);
            } else {
                $result[$id] = array(
                    'id' => $id,
                    'name' => $g['name'],
                    'groups' => array($group)
                );
            }
        }

        $result = array_column($result, null);
        return ['groups' => $groups, 'groupings' => $result];
    }

    public function findLabsByCharonLaterEqualToday(Request $request)
    {
        $charonId = $request->route('charon');
        return \DB::table('charon_lab')  // id, start, end
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id') // id, lab_id, charon_id
        ->where('charon_id', $charonId)
            ->where('end', '>=', Carbon::now())
            ->select('charon_defense_lab.id', 'start', 'end', 'name', 'course_id')
            ->get();
    }

    /**
     * @param Course $course (not used)
     * @param Lab $lab
     *
     * @return int
     */
    public function countRegistrations(Course $course, Lab $lab)
    {
        $start = $this->request['start'] ? Carbon::parse($this->request['start'])->format('Y-m-d H:i:s') : null;
        $end = $this->request['end'] ? Carbon::parse($this->request['end'])->format('Y-m-d H:i:s') : null;
        $charons = $this->request['charons'] ?? null;
        $teachers = $this->request['teachers'] ?? null;
        return $this->labRepository->countRegistrations($lab->id, $start, $end, $charons, $teachers);
    }

    public function queueStatusBeforeLabBeginning(Request $request,int $charon)
    {
        $userId = $request->input("user_id");
        $labId = $request->input("lab_id");
        $middleDefTime=null;

        //**** REGISTRATIONS ****

        //get list of registrations
        $result["registrations"] = \DB::table('charon_defenders')
            ->join("charon", "charon.id", "charon_defenders.charon_id")
            ->where("defense_lab_id", $labId)
            ->where("progress","Waiting")
            ->select("charon.name as charon_name", "charon.defense_duration as charon_length", "student_id")
            ->get();

        //get number of teachers assigned to lab
        $teachers_num = \DB::table('charon_lab_teacher')
            ->where("lab_id", $labId)
            ->count();

        //Get times when lab starts and ends
        $labTime = \DB::table('charon_lab')
            ->where("id",$labId)
            ->select("start","end")
            ->first();

        //Format date
        foreach ($labTime as $key => $date)
        {
            $labTime->$key = strtotime($labTime->$key);
        }

        foreach ($result["registrations"] as $key => $reg)
        {
            //if student id equals to user id, then return username as field, else set it null
            if($reg->student_id == $userId)
            {
                $reg->student_name = \DB::table('user')
                    ->where("id", $userId)
                    ->select("username")
                    ->first()->username;
            }
            else
            {
                $reg->student_name = null;
            }

            //get length of this charon and sum to $middleDeftime
            $middleDefTime += $reg->charon_length;

            //show position in queue
            $reg->queue_pos = $key+1;

        }

        //Get average defense length
        $middleDefTime = $middleDefTime/count($result);

        //Calculate approximate time and delete not needed variables
        foreach ($result["registrations"] as $reg)
        {
            $move = floor($reg->queue_pos-1/$teachers_num) * $middleDefTime;
            $reg->approxStartTime = date("d \of F H:i", $labTime->start + $move * 60);
            unset($reg->charon_length);
            unset($reg->student_id);
        }

        //**** TEACHERS AND ONGOING DEFENCES ****
        //get teachers who have labs with registrations on them
        $result["teachers"] = \DB::table('charon_defenders')
            ->join("user", "user.id", "charon_defenders.teacher_id",null,"left")
            ->whereNotNull("charon_defenders.teacher_id")
            ->select("user.username as teacher_name","user.id")
            ->distinct()
            ->get();

        //get currently ongoing labs for teacher
        if(count($result["teachers"])>0) {
            foreach ($result["teachers"] as $teacher)
            {
                $teacher->currently_defending_registration_id = \DB::table('charon_defenders')
                    ->where("teacher_id",$teacher->id)
                    ->where("progress","Defending")
                    ->select("id")
                    ->first();
                if($teacher->currently_defending_registration_id)
                {
                    $teacher->currently_defending_registration_id = $teacher->currently_defending_registration_id->id;
                }
                unset($teacher->id);
            }
        }

        return $result;

    }

}
