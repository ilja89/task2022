<?php

use PHPMD\AbstractRule;
use PHPMD\AbstractNode;
use PHPMD\Rule\MethodAware;
use PDepend\Source\AST\AbstractASTNode;
use PDepend\Source\AST\ASTArguments;
use PDepend\Source\AST\ASTExpression;
use PDepend\Source\AST\ASTVariable;
use Illuminate\Database\Query\Builder;

/*
 * This rule does NOT detect using variables injected into double-quoted strings (eg. "select * from $myVariable")
 * This is because phpmd for some reason detects such strings as empty ("")
 */
class RawQueryBindingsRule extends AbstractRule implements MethodAware
{

    const MESSAGE = "\nA method {0} uses a variant of DB raw() without bindings: \n{1}\n";
    protected $suspectedNodes = [];

    public function apply(AbstractNode $node)
    {
        $methodNode = $node->getNode();
        $this->findRawMethodUsages($methodNode);

        if (count($this->suspectedNodes)) {
            // Two arguments get passed into self::MESSAGE via {0} and {1}
            $messageArgs = [
                $node->getName(),
                $this->createViolationDetailsString($this->suspectedNodes),
            ];
            $this->setMessage(self::MESSAGE);
            $this->addViolation($node, $messageArgs);
        }

        // Empty the array for next method
        $this->suspectedNodes = [];
    }

    protected function findRawMethodUsages($node)
    {
        /** @var AbstractASTNode $childNode */
        foreach ($node->getChildren() as $childNode) {
            $childNodeImage = $childNode->getImage();

            if ($this->stringIsQueryBuilderRawMethod($childNodeImage)) {
                // Check if this node has arguments
                $rawMethodArgsNode = $childNode->getFirstChildOfType(ASTArguments::class);

                // Get the arguments, if they exist
                if ($rawMethodArgsNode) {
                    // The first (index 0) argument is the SQL itself
                    // The second (index 1) argument is the array of SQL bindings
                    $rawMethodArgs = $rawMethodArgsNode->getChildren();
                    $sqlArgument = $rawMethodArgsNode->getChild(0);

                    $isWithoutBindings = count($rawMethodArgs) === 1;
                    $isExpressionOrVariable =
                        $sqlArgument instanceof ASTExpression ||
                        $sqlArgument instanceof ASTVariable;

                    if ($isWithoutBindings && $isExpressionOrVariable) {
                        // If the node is suspected to have improper usage, add it to an array
                        array_push($this->suspectedNodes, $childNode);
                    }
                }
            }
            $this->findRawMethodUsages($childNode);
        }
    }

    protected function stringIsQueryBuilderRawMethod($inputString): bool
    {
        return collect($this->getQueryBuilderRawMethods())
            ->contains(function ($method) use ($inputString) {
                return $inputString === $method;
            });
    }

    protected function getQueryBuilderRawMethods(): array
    {
        $queryBuilderMethods = get_class_methods(Builder::class);

        return array_filter($queryBuilderMethods, function ($method) {
            return strpos(strtolower($method), 'raw') !== false;
        });
    }

    protected function createViolationDetailsString(array $violatingNodes): string
    {
        return implode("\n", array_map(function ($violatingNode) {
            return $violatingNode->getImage() . ' on line ' . $violatingNode->getStartLine();
        }, $violatingNodes));
    }
}