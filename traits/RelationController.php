<?php namespace RBIn\Shop\Traits;

trait RelationController {

	public $relationConfig = 'relation.yaml';

	public function bootRelationController() {
		$this->implement[] = 'Backend.Behaviors.RelationController';
	}

}