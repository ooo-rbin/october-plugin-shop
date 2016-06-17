<?php namespace RBIn\Shop\Traits;

use Backend\Classes\Controller;
use ReflectionMethod;

/**
 *
 * @package RBIn\Shop\Traits
 */
trait PluginIntegrator {

	/**
	 * Расширяет методом класса
	 * @param string $class Имя расширяемого класса
	 */
	protected function extendClass($class) {
		$class::extend($this->getHandler('extend' . class_basename($class)));
	}

	/**
	 * Возвращает замыкание метода класса
	 * @param string $method Имя метода класса
	 * @return callable
	 */
	protected function getHandler($method) {
		return (new ReflectionMethod($this, $method))->getClosure($this);
	}

}