<?php
 namespace MailPoetVendor\Doctrine\ORM\Query\AST; if (!defined('ABSPATH')) exit; class SubselectFromClause extends \MailPoetVendor\Doctrine\ORM\Query\AST\Node { public $identificationVariableDeclarations = array(); public function __construct(array $identificationVariableDeclarations) { $this->identificationVariableDeclarations = $identificationVariableDeclarations; } public function dispatch($sqlWalker) { return $sqlWalker->walkSubselectFromClause($this); } } 