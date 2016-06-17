<?php namespace RBIn\Shop\Traits;

trait IndexController {

	public function index() {
		$this->pageTitle = "rbin.shop::lang.{$this->name}.label";
	}

	public function makeIndexView() {
		return $this->makePartial('index', $this->vars);
	}

}