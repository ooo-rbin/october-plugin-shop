<?php namespace RBIn\Shop\Classes;

use Cms\Classes\ComponentBase;

abstract class Component extends ComponentBase {

	public static function getComponentName($plural = false) {
		return 'store' . str_replace('\\', '', ($plural) ? str_plural(class_basename(static::class)) : class_basename(static::class));
	}

}