<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;
use RBIn\Shop\Enums\RuleByApplicability;
use October\Rain\Database\Builder;

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
		$this->morphedByMany['source'] = [];
		foreach (RuleByApplicability::getConstants() as $class) {
			$this->morphedByMany['source'][$class::TABLE] = [
				$class,
				'table' => RuledSource::TABLE,
				'otherKey' => $this->getForeignNames(Rule::class)['column'],
				'name' => 'sources',
				'order' => $this->getSortName(Rule::class),
			];
		}
		//
		parent::__construct($attributes);
	}

	public function scopeIsApplied(Builder $query) {
		return $query->where('show', '=', 1)->where('global', '<>', 1);
	}

	public function scopeIsGlobal(Builder $query) {
		return $query->orderBy(static::SORT_ORDER)->where('global', '=', 1);
	}

}