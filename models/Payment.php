<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use System\Models\File;
use October\Rain\Database\Traits\Sortable;

/**
 * Метод оплаты заказа/доставки.
 * @package RBIn\Shop\Models
 */
class Payment extends Model {

	const TABLE = 'rbin_shop_payments';

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
		$this->attachOne['picture'] = [
			File::class
		];
		$this->hasMany[Order::TABLE] = [
			Order::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
			'order' => Order::CREATED_AT . ' desc',
		];
		$this->belongsTo[Rule::TABLE] = [
			Rule::class,
			'key' => $this->getForeignNames(Rule::class)['column'],
			'otherKey' => Rule::KEY,
			'order' => Rule::SORT_ORDER . ' asc',
		];
		//
		parent::__construct($attributes);
	}

}