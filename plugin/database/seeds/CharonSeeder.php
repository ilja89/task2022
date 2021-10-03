<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSection;
use TTU\Charon\Models\Preset;
use TTU\Charon\Models\PresetGrade;
use TTU\Charon\Repositories\PresetsRepository;
use TTU\Charon\Services\CreateCharonService;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\ModuleService;

class CharonSeeder extends Seeder
{
    /** @var PresetsRepository */
    private $presetRepository;

    /** @var CreateCharonService */
    private $charonService;

    /** @var GrademapService */
    private $grademapService;

    /** @var GradebookService */
    private $gradebookService;

    /** @var ModuleService */
    private $moduleService;

    /**
     * CharonSeeder constructor.
     * @param PresetsRepository $presetRepository
     * @param CreateCharonService $charonService
     * @param GrademapService $grademapService
     * @param GradebookService $gradebookService
     * @param ModuleService $moduleService
     */
    public function __construct(
        PresetsRepository $presetRepository,
        CreateCharonService $charonService,
        GrademapService $grademapService,
        GradebookService $gradebookService,
        ModuleService $moduleService
    ) {
        $this->presetRepository = $presetRepository;
        $this->charonService = $charonService;
        $this->grademapService = $grademapService;
        $this->gradebookService = $gradebookService;
        $this->moduleService = $moduleService;
    }

    /**
     * Create Charons under an existing course.
     *
     * @return void
     */
    public function run()
    {
        $courseId = (int) $this->command->ask('Enter course ID');

        $course = Course::find($courseId);
        if (!$course) {
            $this->command->error('Course with ID ' . $courseId . ' not found');
            return;
        }

        $preset = $this->getPreset($courseId);
        if (!$preset) {
            $this->command->error('Unable to find any general or course specific presets, please create some first');
            return;
        }

        $section = $this->getSection($courseId);
        $charonModuleId = $this->moduleService->getModuleId();

        $count = (int) $this->command->ask('Enter a number of Charons', 1);

        /** @var Charon[]|Model[] $charons */
        $charons = factory(Charon::class, $count)->create(['course' => $courseId, 'category_id' => null]);

        $moduleIds = collect($charons)
            ->map(function ($charon) use($preset, $courseId, $section, $charonModuleId) {
                return $this->saveCharon($charon, $preset, $courseId, $section->id, $charonModuleId);
            })->implode(',');

        if (!empty($section->sequence)) {
            $moduleIds = $section->sequence . ',' . $moduleIds;
        }

        $section->sequence = $moduleIds;
        $section->save();

        $this->clearCache($courseId);
    }

    /**
     * @param int $courseId
     * @return Preset|Model|null
     */
    private function getPreset(int $courseId)
    {
        /** @var Preset[]|Collection $presets */
        $presets = $this->presetRepository->getPresetsByCourse($courseId);
        if (!$presets) {
            return null;
        }

        $presetOptions = $presets->mapWithKeys(function ($preset) {
            return [$preset->id => $preset->name . ' [' . $preset->id . ']'];
        })->all();

        $answer = $this->command->choice('Choose a preset', $presetOptions);
        $presetId = (int) substr($answer, strrpos($answer, '[') + 1, -1);

        return $presets->where('id', $presetId)->first();
    }

    /**
     * @param int $courseId
     * @return CourseSection
     */
    private function getSection(int $courseId)
    {
        $sections = CourseSection::where('course', $courseId)->orderBy('section')->get();

        $sectionOptions = $sections->mapWithKeys(function ($section) {
            $name = $section->name ? $section->name : 'Topic ' . $section->section;
            $visibility = $section->visible ? 'visible' : 'not visible';
            return [$section->id => $name . ' (' . $visibility .  ') [' . $section->id . ']'];
        })->all();

        $answer = $this->command->choice('Choose a section', $sectionOptions);
        $sectionId = (int) substr($answer, strrpos($answer, '[') + 1, -1);

        return $sections->firstWhere('id', $sectionId);
    }

    /**
     * @param Charon|Model $charon
     * @param Preset|Model $preset
     * @param int $courseId
     * @param int $sectionId
     * @param int $moduleId
     * @return int Course module ID
     */
    private function saveCharon(Charon $charon, Preset $preset, int $courseId, int $sectionId, int $moduleId)
    {
        $categoryId = $this->charonService->addCategoryForCharon($charon, $courseId);
        $charon->category_id = $categoryId;
        $charon->tester_extra = $preset->tester_extra;
        $charon->system_extra = $preset->system_extra;
        $charon->grading_method_code = $preset->grading_method_code;
        $charon->save();

        $module = CourseModule::create([
            'course' => $courseId,
            'module' => $moduleId,
            'instance' => $charon->id,
            'section' => $sectionId,
            'added' => Carbon::now()->timestamp,
            'completion' => 1
        ]);

        $this->addPresetGrades($charon, $preset);
        $this->addFormula($charon, $preset);

        return $module->id;
    }

    /**
     * ID Number: An arbitrary string of characters used to refer to this grade item in Formulas.
     * If set, it must be unique.
     *
     * Partial implementation from
     * GradebookService::moveGradeItemToCategory
     * InstanceController::postCourseModuleCreatedOrUpdated()
     *
     * @param Charon $charon
     * @param Preset $preset
     */
    private function addPresetGrades(Charon $charon, Preset $preset)
    {
        $preset->presetGrades()->each(function ($grade) use ($charon) {
            /** @var PresetGrade $grade */
            $name = $this->getGradeName($grade, $charon);

            $this->grademapService->createGrademapWithGradeItem(
                $charon,
                $grade->grade_type_code,
                $charon->course,
                [
                    'grademap_name' => $name . $grade->grade_name,
                    'max_points' => $grade->max_result,
                    'id_number' => $name . $grade->id_number_postfix
                ]
            );
        });

         $this->linkGrademap($charon, $preset);
    }

    /**
     * Retry linking grade maps and grade items if not all of them were processed in time.
     *
     * Seems to be a race over DB i/o.
     *
     * @param Charon $charon
     * @param Preset $preset
     * @param int $retry
     */
    private function linkGrademap(Charon $charon, Preset $preset, $retry = 3)
    {
        if ($retry < 1) {
            $this->command->error('Unable to link all grades to Charon ' . $charon->id . ', please do that manually');
        }

        $expectedLinks = $preset->presetGrades()->count();
        $linked = 0;

        $charon->load('grademaps');

        $this->grademapService->linkGrademapsAndGradeItems($charon);

        foreach ($charon->grademaps as $grademap) {
            $this->gradebookService->updateGradeItem($grademap->grade_item_id, [
                'categoryid' => $charon->category_id,
                'needsupdate' => 0,
            ]);
            $linked++;
        }

        if ($expectedLinks == $linked) {
            $this->command->info("Successfully linked grades for Charon " . $charon->id);
            return;
        }

        $this->command->warn('Found only [' . $linked . '/' . $expectedLinks . '] grades. Attempts remaining [' . $retry. ']');
        if ($this->command->confirm('Do you wish to continue?')) {
            $this->linkGrademap($charon, $preset, $retry - 1);
        } else {
            $this->linkGrademap($charon, $preset, 0);
        }
    }

    /**
     * @param Charon $charon
     * @param Preset $preset
     */
    private function addFormula(Charon $charon, Preset $preset)
    {
        $formula = $preset->presetGrades()->get()->map(function ($grade) use ($charon) {
            return '[[' . $this->getGradeName($grade, $charon)  . $grade->id_number_postfix . ']]';
        })->all();

        $categoryGradeItem = $this->gradebookService->getGradeItemByCategoryId($charon->category_id);

        $this->gradebookService->updateGradeItem($categoryGradeItem->id, [
            'grademax' => $preset->max_result,
            'needsupdate' => 0,
            'calculation' => '=' . implode(' * ', $formula)
        ]);
    }

    /**
     * @param PresetGrade $grade
     * @param Charon $charon
     * @return mixed
     */
    private function getGradeName(PresetGrade $grade, Charon $charon)
    {
        if ($grade->grade_name_prefix_code == 1) {
            return $charon->project_folder;
        }

        $name = preg_replace("/ +/", '_', $charon->name);
        $name = preg_replace("/\W+/", '', $name);
        $name = preg_replace("/_+/", '_', $name);
        return strtolower($name);
    }

    /**
     * Moodle caches course module and section data
     *
     * @param $courseId
     */
    private function clearCache($courseId)
    {
        global $CFG;
        require_once $CFG->dirroot . '/lib/modinfolib.php';

        rebuild_course_cache($courseId);
    }
}
