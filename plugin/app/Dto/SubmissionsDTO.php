<?php


namespace TTU\Charon\Dto;


use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;

class SubmissionsDTO
{
    /** @var Submission */
    public $submission;

    /** @var SubmissionFile[] */
    public $submissionFiles;

    /**
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     */
    public function setSubmission(Submission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * @return array
     */
    public function getSubmissionFiles(): array
    {
        //TODO Check if it still get correct array
        return $this->submissionFiles;
    }

    /**
     * @param SubmissionFile[] $submissionFiles
     */
    public function setSubmissionFiles(array $submissionFiles)
    {
        $this->submissionFiles = $submissionFiles;
    }


}