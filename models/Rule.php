<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;
use RBIn\Shop\Enums\RuleByApplicability;

/**
 * Тип свойства элемента каталога продукции.
 * @package RBIn\Shop\Models
 */
class Rule extends Model {

	const TABLE = 'rbin_shop_rules';

	use Sortable;

	const SORT_ORDER = 'order';

	public function getSortOrderAttribute() {
		return $this->{static::SORT_ORDER};
	}

	public function getApplicabilityAttribute($value) {
		return array_intersect(RuleByApplicability::getConstants(), explode(',', $value));
	}

	public function setApplicabilityAttribute(array $value) {
		$this->attributes['applicability'] = implode(',', array_intersect(RuleByApplicability::getConstants(), $value));
	}

	public function __construct(array $attributes = []) {
		// Правила
		$this->rules = [
		];
		// Связи
		$this->hasMany[OrderedRule::TABLE] = [
			OrderedRule::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
		];
		$this->belongsToMany[Order::TABLE] = [
			Order::class,
			'table' => OrderedRule::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Order::class)['column'],
			'order' => Order::CREATED_AT . ' desc',
		];
		foreach (RuleByApplicability::getConstants() as $class) {
			$this->morphedByMany[$class::TABLE] = [$class, 'name' => 'sources'];
		}
		//
		parent::__construct($attributes);
	}

}