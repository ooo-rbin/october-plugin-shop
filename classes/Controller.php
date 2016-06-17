<?php namespace RBIn\Shop\Classes;

use Url;
use Backend;
use BackendMenu;
use Backend\Classes\Controller as ControllerBase;

abstract class Controller extends ControllerBase {

	protected $name;

	public function __construct() {
		$this->name = str_replace('\\', '', snake_case(class_basename(static::class)));
		$this->requiredPermissions[] = "rbin.shop.{$this->name}.*";
		parent::__construct();
		$this->assetPath = Url::to('/plugins/rbin/shop/assets');
		$this->addJs('shop.js', 'core');
		$this->addCss('shop.css', 'core');
		BackendMenu::setContext('RBIn.Shop', 'rbin_shop', $this->name);
	}

	public function makeView($view) {
		return (method_exists($this, "make${view}View")) ? $this->makeViewContent(call_user_func([$this, "make${view}View"])) : parent::makeView($view);
	}

	public function makePartial($partial, $params = [], $throwException = true) {
		return (method_exists($this, "make${partial}Partial")) ? call_user_func([$this, "make${partial}Partial"], $params, $throwException) : parent::makePartial($partial, $params, $throwException);
	}

}