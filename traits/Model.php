<?php namespace RBIn\Shop\Traits;

use Doctrine\DBAL\Exception\InvalidArgumentException;

trait Model {

	use ForeignNames;

	public function getSetField($value, $enumClass) {
		return array_intersect($enumClass::getConstants(), explode(',', $value));
	}

	public function setSetField($value, $enumClass) {
		if (is_string($value)) {
			$value = $this->getSetField($value, $enumClass);
		}
		if (is_array($value)) {
			$this->attributes['logically'] = implode(',', array_intersect($enumClass::getConstants(), $value));
		} else {
			throw new InvalidArgumentException();
		}
	}

}