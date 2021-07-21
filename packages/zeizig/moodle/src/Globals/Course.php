<?php

namespace Zeizig\Moodle\Globals;

/**
 * Class Course.
 * Wrapper for the $COURSE global.
 *
 * @package Zeizig\Moodle\Globals
 */
class Course
{
    /** @var \stdClass */
    protected $course;

    /**
     * Course constructor.
     */
    public function __construct()
    {
        global $COURSE;
        $this->course = $COURSE;
    }

    /**
     * Get the current course short name.
     *
     * @return string
     */
    public function getCourseShortName()
    {
        return $this->course->shortname;
    }

    /**
     * Get the current course full name.
     *
     * @return string
     */
    public function getCourseFullName()
    {
        return $this->course->fullname;
    }

    /**
     * Get the current course ID.
     *
     * @return integer
     */
    public function getCourseId()
    {
        return $this->course->id;
    }
    
    /* Function needed to get list of students related to exact course
     * 
     * @param int $courseId
     * 
     * @return array $nameList
     */
     
    public function getNamesOfStudentsRelatedToCourse(int $courseId)
    {
        $nameList = null;
        $studentList=json_decode(json_encode($this->studentsRepository->searchStudentsByCourseAndKeyword($courseId,"")),true);
        for($i=0;$i<count($studentList);$i++)
        {
            $nameList[$i] = $studentList[$i]["username"];
        }
        return $nameList;
    }
}
