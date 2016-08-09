<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use System\Models\File;
use October\Rain\Database\Traits\Sortable;

/**
 * Вариант продукта.
 * @package RBIn\Shop\Models
 */
class Variant extends Model {

	const TABLE = 'rbin_shop_variants';

	use Sortable;

	const SORT_ORDER = 'order';

	public function getSortOrderAttribute() {
		return $this->{static::SORT_ORDER};
	}

	protected $fillable = [
		'title',
		'slug',
		'balance',
		'cost',
	];

	public function __construct(array $attributes = []) {
		// Правила
		$this->rules = [
		];
		// Связи
		$this->attachOne['picture'] = [
			File::class
		];
		$this->belongsTo[Product::TABLE] = [
			Product::class,
			'key' => $this->getForeignNames(Product::class)['column'],
			'otherKey' => Product::KEY,
			'order' => Product::SORT_ORDER . ' asc',
		];
		$this->hasMany[OrderedVariant::TABLE] = [
			OrderedVariant::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
		];
		$this->belongsToMany[Order::TABLE] = [
			Order::class,
			'table' => OrderedVariant::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Order::class)['column'],
			'order' => Order::CREATED_AT . ' desc',
		];
		$this->morphToMany[Rule::TABLE] = [
			Rule::class,
			'scope' => 'isApplied',
			'table' => RuledSource::TABLE,
			'otherKey' => $this->getForeignNames(Rule::class)['column'],
			'name' => 'source',
			'order' => $this->getSortName(Rule::class),
		];
		//
		parent::__construct($attributes);
	}

}