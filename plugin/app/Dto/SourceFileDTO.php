<?php

namespace TTU\Charon\Dto;

class SourceFileDTO
{
    /** @var string */
    private $path;

    /** @var string|null */
    private $contents;

    /**
     * @param string $path
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param string|null $content
     */
    public function setContent(string $content = null)
    {
        $this->contents = $content;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->contents;
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
