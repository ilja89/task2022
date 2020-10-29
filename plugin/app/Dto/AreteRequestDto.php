<?php


namespace TTU\Charon\Dto;

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

	/** @var string */
	private $testingPlatform;

    /**
     * @param string $dockerContentRoot
     * @return AreteRequestDto
     */
    public function setDockerContentRoot(string $dockerContentRoot): AreteRequestDto
    {
        $this->dockerContentRoot = $dockerContentRoot;
        return $this;
    }

    /**
     * @param string $dockerExtra
     * @return AreteRequestDto
     */
    public function setDockerExtra(string $dockerExtra): AreteRequestDto
    {
        $this->dockerExtra = $dockerExtra;
        return $this;
    }

    /**
     * @param string $dockerTestRoot
     * @return AreteRequestDto
     */
    public function setDockerTestRoot(string $dockerTestRoot): AreteRequestDto
    {
        $this->dockerTestRoot = $dockerTestRoot;
        return $this;
    }

    /**
     * @param int $dockerTimeout
     * @return AreteRequestDto
     */
    public function setDockerTimeout(int $dockerTimeout): AreteRequestDto
    {
        $this->dockerTimeout = $dockerTimeout;
        return $this;
    }

    /**
     * @param string $gitStudentRepo
     * @return AreteRequestDto
     */
    public function setGitStudentRepo(string $gitStudentRepo): AreteRequestDto
    {
        $this->gitStudentRepo = $gitStudentRepo;
        return $this;
    }

    /**
     * @param string $gitTestRepo
     * @return AreteRequestDto
     */
    public function setGitTestRepo(string $gitTestRepo): AreteRequestDto
    {
        $this->gitTestRepo = $gitTestRepo;
        return $this;
    }

    /**
     * @param string $hash
     * @return AreteRequestDto
     */
    public function setHash(string $hash): AreteRequestDto
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @param string $systemExtra
     * @return AreteRequestDto
     */
    public function setSystemExtra(string $systemExtra): AreteRequestDto
    {
        if (!empty($systemExtra)) {
            $this->systemExtra = explode(',', $systemExtra);
        }
        return $this;
    }

    /**
     * @param string $testingPlatform
     * @return AreteRequestDto
     */
    public function setTestingPlatform(string $testingPlatform): AreteRequestDto
    {
        $this->testingPlatform = $testingPlatform;
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

}
