<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;

/**
 * Метод оплаты заказа/доставки.
 * @package RBIn\Shop\Models
 */
class Option extends Model {

	const TABLE = 'rbin_shop_options';

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
		$this->belongsTo[Feature::TABLE] = [
			Feature::class,
			'key' => $this->getForeignNames(Feature::class)['column'],
			'otherKey' => Feature::KEY,
			'order' => Feature::SORT_ORDER . ' asc',
		];
		$this->belongsTo[Product::TABLE] = [
			Product::class,
			'key' => $this->getForeignNames(Product::class)['column'],
			'otherKey' => Product::KEY,
			'order' => Product::SORT_ORDER . ' asc',
		];
		//$this->morphMany[Rule::TABLE] = [
		//	Rule::class,
		//	'name' => 'from',
		//	'order' => Product::SORT_ORDER . ' asc',
		//];
		//
		parent::__construct($attributes);
	}

}