<?php

namespace TTU\Charon\Dto;

use Carbon\Carbon;

/**
 * @see https://github.com/envomp/arete-java/blob/master/src/main/java/ee/taltech/arete/java/request/AreteRequestDTO.java
 */
class AreteRequestDto
{
    /** @var string */
    private $dockerContentRoot;

    /** @var string */
    private $dockerExtra;

    /** @var string */
    private $email;

    /** @var string */
    private $dockerTestRoot;

    /** @var int */
    private $dockerTimeout;

    /** @var string */
    private $gitStudentRepo;

    /** @var string */
    private $gitTestRepo;

    /** @var string */
    private $hash;

    /** @var array */
    private $systemExtra;

    /** @var array */
    private $returnExtra;

    /** @var string */
    private $testingPlatform;

    /** @var int */
    private $timestamp;

    /** @var array|SourceFileDTO */
    private $source;

    /** @var array|SourceFileDTO */
    private $testSource;

    /** @var string */
    private $uniid;

    /** @var array */
    private $slugs;

    /**
     * @param string|null $dockerContentRoot
     * @return AreteRequestDto
     */
    public function setDockerContentRoot(string $dockerContentRoot = null): AreteRequestDto
    {
        $this->dockerContentRoot = $dockerContentRoot;
        return $this;
    }

    /**
     * @param string|null $dockerExtra
     * @return AreteRequestDto
     */
    public function setDockerExtra(string $dockerExtra = null): AreteRequestDto
    {
        $this->dockerExtra = $dockerExtra;
        return $this;
    }

    /**
     * @param string|null $email
     * @return AreteRequestDto
     */
    public function setEmail(string $email = null): AreteRequestDto
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string|null $dockerTestRoot
     * @return AreteRequestDto
     */
    public function setDockerTestRoot(string $dockerTestRoot = null): AreteRequestDto
    {
        $this->dockerTestRoot = $dockerTestRoot;
        return $this;
    }

    /**
     * @param int|null $dockerTimeout
     * @return AreteRequestDto
     */
    public function setDockerTimeout(int $dockerTimeout = null): AreteRequestDto
    {
        $this->dockerTimeout = $dockerTimeout;
        return $this;
    }

    /**
     * @param string|null $gitStudentRepo
     * @return AreteRequestDto
     */
    public function setGitStudentRepo(string $gitStudentRepo = null): AreteRequestDto
    {
        $this->gitStudentRepo = $gitStudentRepo;
        return $this;
    }

    /**
     * @param string|null $gitTestRepo
     * @return AreteRequestDto
     */
    public function setGitTestRepo(string $gitTestRepo = null): AreteRequestDto
    {
        $this->gitTestRepo = $gitTestRepo;
        return $this;
    }

    /**
     * @param string|null $hash
     * @return AreteRequestDto
     */
    public function setHash(string $hash = null): AreteRequestDto
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @param string|null $systemExtra
     * @return AreteRequestDto
     */
    public function setSystemExtra(string $systemExtra = null): AreteRequestDto
    {
        if (!empty($systemExtra)) {
            $this->systemExtra = explode(',', $systemExtra);
        }
        return $this;
    }

    /**
     * @param array $returnExtra
     * @return AreteRequestDto
     */
    public function setReturnExtra(array $returnExtra = []): AreteRequestDto
    {
        if (!empty($returnExtra)) {
            $this->returnExtra = $returnExtra;
        }
        return $this;
    }

    /**
     * @param string|null $testingPlatform
     * @return AreteRequestDto
     */
    public function setTestingPlatform(string $testingPlatform = null): AreteRequestDto
    {
        $this->testingPlatform = $testingPlatform;
        return $this;
    }

    /**
     * @param Carbon|string|int|null $timestamp
     * @return AreteRequestDto
     */
    public function setTimestamp($timestamp = null): AreteRequestDto
    {
        if (empty($timestamp)) {
            return $this;
        }
        if ($timestamp instanceof Carbon) {
            $this->timestamp = $timestamp->getTimestamp();
        }
        if (is_string($timestamp)) {
            $this->timestamp = Carbon::parse($timestamp, date_default_timezone_get())->getTimestamp();
        }
        if (is_int($timestamp)) {
            $this->timestamp = $timestamp;
        }
        return $this;
    }

    /**
     * @param array|SourceFileDTO
     * @return AreteRequestDto
     */
    public function setSource(array $sourceFiles = []): AreteRequestDto
    {
        $this->source = $sourceFiles;
        return $this;
    }

    /**
     * @param array|SourceFileDTO $testSource
     * @return AreteRequestDto
     */
    public function setTestSource(array $testSource = []): AreteRequestDto
    {
        $this->testSource = $testSource;
        return $this;
    }

    public function toArray(): array
    {
        $payload = [];
        foreach ($this as $key => $value) {
            if (!empty($value)) {
                $payload[$key] = $value;
            }
        }
        return $payload;
    }

    /**
     * @param string $uniid
     * @return AreteRequestDto
     */
    public function setUniid(string $uniid): AreteRequestDto
    {
        $this->uniid = $uniid;
        return $this;
    }

    /**
     * @param array $slugs
     * @return AreteRequestDto
     */
    public function setSlugs(array $slugs): AreteRequestDto
    {
        $this->slugs = $slugs;
        return $this;
    }
}
