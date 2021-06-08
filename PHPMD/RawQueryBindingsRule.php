<?php

namespace TTU\Charon\PHPMD;

use PHPMD\AbstractRule;
use PHPMD\AbstractNode;
use PHPMD\Rule\MethodAware;
use PDepend\Source\AST\AbstractASTNode;
use PDepend\Source\AST\ASTArguments;
use PDepend\Source\AST\ASTExpression;
use PDepend\Source\AST\ASTVariable;
use PDepend\Source\AST\ASTString;
use PDepend\Source\AST\ASTStatement;
use PDepend\Source\AST\ASTAssignmentExpression;
use PDepend\Source\AST\ASTLiteral;
use PDepend\Source\AST\ASTFormalParameters;
use PDepend\Source\AST\ASTFormalParameter;
use PDepend\Source\AST\ASTVariableDeclarator;
use Illuminate\Database\Query\Builder;

/*
 * This rule does NOT detect using variables injected into double-quoted strings (eg. "select * from $myVariable")
 * This is because phpmd for some reason detects such strings as empty ("")
 */
class RawQueryBindingsRule extends AbstractRule implements MethodAware
{
    const MESSAGE = "\nA method {0} uses a variant of DB raw() without bindings: \n{1}\n";

    protected $variableNodeObjectsInSql = [];

    public function apply(AbstractNode $node)
    {
        $methodNode = $node->getNode();
        $this->findRawMethodUsages($methodNode);

        if (count($this->variableNodeObjectsInSql)) {
            $methodParams = $this->getMethodParams($methodNode);

            foreach ($this->variableNodeObjectsInSql as $variableNodeObject) {
                if (!$variableNodeObject->getIsStringWithVariable()) {
                    $this->getNonLiteralNode($methodNode, $methodParams, $variableNodeObject);
                }
            }

            // Two arguments get passed into self::MESSAGE via {0} and {1}
            $messageArgs = [
                $node->getName(),
                $this->createViolationDetailsString($this->variableNodeObjectsInSql),
            ];
            $this->setMessage(self::MESSAGE);
            $this->addViolation($node, $messageArgs);
        }

        // Empty the array for next method
        $this->variableNodeObjectsInSql = [];
    }


    // If variable, track back to see if is literal in beginning. If not, add to list
    // If expression, add variables inside it to list
    // ASTStatement contains 1 node ASTAssignmentExpression which has to contain ASTVariable on index 0 and ASTLiteral on index 1
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

                    $sqlVariables = [];
                    $sqlStrings = [];
                    if ($sqlArgument instanceof ASTVariable) {
                        $sqlVariables = [$sqlArgument];
                    } elseif ($sqlArgument instanceof ASTExpression) {
                        $sqlVariables = array_filter($sqlArgument->getChildren(), function ($expressionSegment) {
                            return $expressionSegment instanceof ASTVariable;
                        });
                        $sqlStrings = array_filter($sqlArgument->getChildren(), function ($expressionSegment) {
                            return $expressionSegment instanceof ASTString && !$expressionSegment->getImage();
                        });
                    }

                    if ($isWithoutBindings) {
                        // If the node is suspected to have improper usage, add it to an array
                        foreach ($sqlVariables as $sqlVariable) {
                            $sqlVariableObject = new CharonPhpmdNode($sqlVariable);
                            array_push($this->variableNodeObjectsInSql, $sqlVariableObject);
                        }
                        foreach ($sqlStrings as $sqlString) {
                            $sqlVariableObject = new CharonPhpmdNode($sqlString, null, true);
                            array_push($this->variableNodeObjectsInSql, $sqlVariableObject);
                        }
                    }
                }
            }
            $this->findRawMethodUsages($childNode);
        }
    }

    protected function getNonLiteralNode(
        $methodNode,
        $methodParams,
        CharonPhpmdNode $searchableVariableNode,
        $currentPointerNode = null
    ) {
        $currentPointerNode = $currentPointerNode ?? $methodNode;
        $childNodes = $currentPointerNode->getChildren();

        if ($currentPointerNode instanceof ASTVariableDeclarator) {
            if ($currentPointerNode->getImage() === $searchableVariableNode->getImage()) {
                $searchableVariableNode->setIsMethodArgument(true);
                return;
            }
        }

        if ($currentPointerNode instanceof ASTStatement) {
            $assignmentNode = $currentPointerNode->getFirstChildOfType(ASTAssignmentExpression::class);
            // We are searching for a statement of type AssignmentExpression
            if ($assignmentNode && $assignmentNode->getStartLine() < $searchableVariableNode->getNode()->getStartLine()) {
                // The left hand must be the same image as the variable node we are searching for
                // The right hand should end up being a Literal
                // If it isn't, it means
                $assignmentLeftHand = $assignmentNode->getChild(0);
                $assignmentRightHand = $assignmentNode->getChild(1);

                if ($assignmentLeftHand->getImage() === $searchableVariableNode->getImage()) {
                    if (!($assignmentRightHand instanceof ASTLiteral)) {
                        $nestedVariableNode = $assignmentRightHand->getFirstChildOfType(ASTVariable::class);
                        $newTraceNode = new CharonPhpmdNode($nestedVariableNode, $searchableVariableNode);
                        if ($newTraceNode->getImage() !== $searchableVariableNode->getRootParent()->getImage() && $newTraceNode->getImage() !== '$this') {
                            $searchableVariableNode->pushToTrace($newTraceNode);
                            $this->getNonLiteralNode($methodNode, $methodParams, $newTraceNode);
                        }
                        return;
                    }
                }
            }
        }

        foreach ($childNodes as $childNode) {
            $this->getNonLiteralNode($methodNode, $methodParams, $searchableVariableNode, $childNode);
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

    protected function getMethodParams($node): array
    {
        $formalParamsObject = $node->getFirstChildOfType(ASTFormalParameters::class);
        $formalParamsObjects = $formalParamsObject->getChildren();
        return array_map(function (ASTFormalParameter $formalParam) {
            /** @var ASTVariableDeclarator $variableDeclarator */
            $variableDeclarator = $formalParam->getFirstChildOfType(ASTVariableDeclarator::class);
            return $variableDeclarator->getImage();
        }, $formalParamsObjects);
    }

    protected function createViolationDetailsString(array $violatingNodes): string
    {
        return implode("\n", array_map(function ($violatingNode) {
            return $violatingNode->getHumanReadableString();
        }, $violatingNodes));
    }
}
