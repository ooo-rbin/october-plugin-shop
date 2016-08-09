<?php namespace RBIn\Shop\Traits;

trait Deferred {

	protected $deferredRelations = [];

	public function deferred($relation, $model) {
		if (!isset($this->deferredRelations[$relation])) {
			$this->deferredRelations[$relation] = [];

		}
		$this->deferredRelations[$relation][] = $model;
	}

}