<?php namespace RBIn\Shop\Traits;

trait ListController {

	public $listConfig = [];

	public function bootListController() {
		$this->implement[] = 'Backend.Behaviors.ListController';
		$this->listConfig['list'] = 'list.yaml';
	}

	public function index() {
		$this->bodyClass = 'slim-container';
		return $this->asExtension('ListController')->index();
	}

	public function makeIndexView() {
		return $this->listRender('list');
	}

}