<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;

/**
 * Свойство элемента каталога продукции.
 * @package RBIn\Shop\Models
 */
class OrderedRule extends Model {

	const TABLE = 'rbin_shop_ordered_rules';

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
		$this->belongsTo[Order::TABLE] = [
			Order::class,
			'key' => $this->getForeignNames(Order::class)['column'],
			'otherKey' => Order::KEY,
			'order' => Order::CREATED_AT . ' desc',
		];
		//$this->belongsTo[Rule::TABLE] = [
		//	Rule::class,
		//	'key' => $this->getForeignNames(Rule::class)['column'],
		//	'otherKey' => Rule::KEY,
		//	'order' => Rule::SORT_ORDER . ' asc',
		//];
		//
		parent::__construct($attributes);
	}

}