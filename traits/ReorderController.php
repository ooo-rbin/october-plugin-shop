<?php namespace RBIn\Shop\Traits;

trait ReorderController {

	public $reorderConfig = 'reorder.yaml';

	public function bootReorderController() {
		$this->implement[] = 'Backend.Behaviors.ReorderController';
	}

	public function reorder() {
		$this->asExtension('ReorderController')->reorder();
	}

	public function makeReorderView() {
		return $this->makePartial('$/rbin/shop/partials/_reorder.htm', $this->asExtension('ReorderController')->vars);
	}

}