<?php namespace RBIn\Shop\Classes;

use Illuminate\Database\Eloquent\Builder;
use ReflectionClass;

/**
 * Абстракция перечисления.
 * Вариант использования:
 * 1) члены множества определяются как константы унаследованного класса;
 * 2) значение константы - строка ссылки на перевод
 * @package RBIn\Shop\Classes
 */
abstract class Enum {

	/**
	 * Возвращает асоциативный массив констант, где ключ - имя константы, а значение - значение константы.
	 * @param boolean $trans Флаг необходимости немедленного перевода значений
	 * @return array
	 */
	final static public function getConstants($trans = false) {
		static $constants;
		if (!isset($constants)) {
			$reflection = new ReflectionClass(static::class);
			$constants = $reflection->getConstants();
		}
		return ($trans) ? array_map('trans', $constants) : $constants;
	}

	/**
	 * Находит имя константы по её значению
	 * @param string $value Значение
	 * @return string
	 */
	final static public function getConstant($value) {
		return array_search($value, static::getConstants());
	}

	final static public function orderBy(Builder $query, $field, $direction = 'desc') {
		$arr = array_keys(static::getConstants());
		if ($direction == 'asc') {
			$arr = array_reverse($arr);
		}
		$enum = implode('\',\'', $arr);
		return $query->orderByRaw("FIELD(`${field}`,'${enum}')");
	}

	final static public function trans($name) {
		$constants = static::getConstants();
		return (isset($constants[$name])) ? trans($constants[$name]) : false;
	}

}