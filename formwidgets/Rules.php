<?php namespace RBIn\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;

class Rules extends FormWidgetBase {

	const CODE = 'rules';

	protected $defaultAlias = self::CODE;

	//public $mode = 'tab';

	public function init() {
		//$this->fillFromConfig(['mode']);
	}

	public function prepareVars() {
		//$this->vars['mode'] = $this->mode;
		$this->vars['stretch'] = $this->formField->stretch;
		$this->vars['size'] = $this->formField->size;
		$this->vars['name'] = $this->formField->getName();
		//

		//$this->vars['value'] = $this->model;
	}

	public function render() {
		$this->prepareVars();
		return '&nbsp;';//$this->makePartial('container');
	}

	protected function loadAssets()	{
		$this->addCss('model.css', 'core');
	}

	public function widgetDetails() {
		return [
			'name' => 'rbin.shop::lang.rules.label',
			'description' => 'rbin.shop::lang.rules.description',
		];
	}

}