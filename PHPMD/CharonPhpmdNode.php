<?php

namespace TTU\Charon\PHPMD;

use PDepend\Source\AST\ASTNode;

/*
 * This rule does NOT detect using variables injected into double-quoted strings (eg. "select * from $myVariable")
 * This is because phpmd for some reason detects such strings as empty ("")
 */
class CharonPhpmdNode
{
    protected $image;
    protected $node;
    protected $parent;
    protected $trace;
    protected $isMethodArgument;
    protected $isStringWithVariable;

    public function __construct(
        ASTNode $node,
        CharonPhpmdNode $parent = null,
        bool $isStringWithVariable = false,
        array $trace = [],
        bool $isMethodArgument = false
    ) {
        $this->image = $node->getImage();
        $this->node = $node;
        $this->parent = $parent;
        $this->trace = $trace;
        $this->isMethodArgument = $isMethodArgument;
        $this->isStringWithVariable = $isStringWithVariable;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getNode(): ASTNode
    {
        return $this->node;
    }

    public function getParent(): CharonPhpmdNode
    {
        return $this->parent;
    }

    public function getRootParent(): CharonPhpmdNode
    {
        if (!$this->parent) {
            return $this;
        } else {
            return $this->parent->getRootParent();
        }
    }

    public function getTrace(): array
    {
        return $this->trace;
    }

    public function getIsStringWithVariable(): bool
    {
        return $this->isStringWithVariable;
    }

    public function getIsMethodArgument(): bool
    {
        return $this->isMethodArgument;
    }

    public function setIsMethodArgument(bool $isMethodArgument)
    {
        $this->isMethodArgument = $isMethodArgument;
    }

    public function pushToTrace(CharonPhpmdNode $node)
    {
        array_push($this->trace, $node);
    }

    public function getHumanReadableString(): string
    {
        if ($this->isStringWithVariable) {
            return 'A string with injected variable on line ' . $this->node->getStartLine();
        }
        $humanReadableTraces = array_map(function ($traceObject) {
            return $traceObject->getHumanReadableString();
        }, $this->getTrace());

        $humanReadableTracesString = implode(' and ', $humanReadableTraces);

        $output = $this->image . ' on line ' . $this->node->getStartLine();

        if ($humanReadableTracesString) {
            $output .= ' traces back to ' . $humanReadableTracesString;
        }
        if ($this->isMethodArgument) {
            $output .= ' that is a method argument';
        }
        return $output;
    }
}
