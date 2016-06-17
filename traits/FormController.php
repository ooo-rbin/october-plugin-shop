<?php namespace RBIn\Shop\Traits;

trait FormController {

	public $formConfig = 'form.yaml';

	public function bootFormController() {
		$this->implement[] = 'Backend.Behaviors.FormController';
	}

	public function create() {
		$this->bodyClass = 'compact-container';
		return $this->asExtension('FormController')->create();
	}

	public function update($id) {
		$this->bodyClass = 'compact-container';
		return $this->asExtension('FormController')->update($id);
	}

	public function preview($id) {
		$this->bodyClass = 'compact-container';
		return $this->asExtension('FormController')->preview($id);
	}

	public function makeCreateView() {
		return $this->makeFormView();
	}

	public function makeUpdateView() {
		return $this->makeFormView();
	}

	public function makePreviewView() {
		return $this->makeFormView();
	}

	public function makeFormView() {
		return $this->makePartial('$/rbin/shop/partials/_form.htm', $this->vars);
	}

}