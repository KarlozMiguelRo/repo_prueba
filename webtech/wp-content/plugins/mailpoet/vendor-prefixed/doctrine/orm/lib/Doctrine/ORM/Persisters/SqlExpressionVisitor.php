<?php
 namespace MailPoetVendor\Doctrine\ORM\Persisters; if (!defined('ABSPATH')) exit; use MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata; use MailPoetVendor\Doctrine\Common\Collections\Expr\ExpressionVisitor; use MailPoetVendor\Doctrine\Common\Collections\Expr\Comparison; use MailPoetVendor\Doctrine\Common\Collections\Expr\Value; use MailPoetVendor\Doctrine\Common\Collections\Expr\CompositeExpression; use MailPoetVendor\Doctrine\ORM\Persisters\Entity\BasicEntityPersister; class SqlExpressionVisitor extends \MailPoetVendor\Doctrine\Common\Collections\Expr\ExpressionVisitor { private $persister; private $classMetadata; public function __construct(\MailPoetVendor\Doctrine\ORM\Persisters\Entity\BasicEntityPersister $persister, \MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata $classMetadata) { $this->persister = $persister; $this->classMetadata = $classMetadata; } public function walkComparison(\MailPoetVendor\Doctrine\Common\Collections\Expr\Comparison $comparison) { $field = $comparison->getField(); $value = $comparison->getValue()->getValue(); if (isset($this->classMetadata->associationMappings[$field]) && $value !== null && !\is_object($value) && !\in_array($comparison->getOperator(), array(\MailPoetVendor\Doctrine\Common\Collections\Expr\Comparison::IN, \MailPoetVendor\Doctrine\Common\Collections\Expr\Comparison::NIN))) { throw \MailPoetVendor\Doctrine\ORM\Persisters\PersisterException::matchingAssocationFieldRequiresObject($this->classMetadata->name, $field); } return $this->persister->getSelectConditionStatementSQL($field, $value, null, $comparison->getOperator()); } public function walkCompositeExpression(\MailPoetVendor\Doctrine\Common\Collections\Expr\CompositeExpression $expr) { $expressionList = array(); foreach ($expr->getExpressionList() as $child) { $expressionList[] = $this->dispatch($child); } switch ($expr->getType()) { case \MailPoetVendor\Doctrine\Common\Collections\Expr\CompositeExpression::TYPE_AND: return '(' . \implode(' AND ', $expressionList) . ')'; case \MailPoetVendor\Doctrine\Common\Collections\Expr\CompositeExpression::TYPE_OR: return '(' . \implode(' OR ', $expressionList) . ')'; default: throw new \RuntimeException("Unknown composite " . $expr->getType()); } } public function walkValue(\MailPoetVendor\Doctrine\Common\Collections\Expr\Value $value) { return '?'; } } 