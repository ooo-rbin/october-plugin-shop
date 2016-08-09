<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Варианты продуктов в заказе.
 * @package RBIn\Shop\Models
 */
class OrderedVariant extends Model {

	const TABLE = 'rbin_shop_ordered_variants';

	use Sortable;

	const SORT_ORDER = 'order';

	public function getSortOrderAttribute() {
		return $this->{static::SORT_ORDER};
	}

	protected $fillable = [
		'title',
		'variant_id',
		'product_id',
		'amount',
		'units',
		'cost',
	];

	protected $visible = [
		'title',
		'variant_id',
		'product_id',
		'amount',
		'units',
		'cost',
	];

	protected $casts = [
		'title' => 'string',
		'variant_id' => 'integer',
		'product_id' => 'integer',
		'amount' => 'integer',
		'units' => 'string',
		'cost' => 'float',
	];

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
		$this->belongsTo[Variant::TABLE] = [
			Variant::class,
			'key' => $this->getForeignNames(Variant::class)['column'],
			'otherKey' => Variant::KEY,
			'order' => Variant::SORT_ORDER . ' asc',
		];
		$this->belongsTo[Product::TABLE] = [
			Product::class,
			'key' => $this->getForeignNames(Product::class)['column'],
			'otherKey' => Product::KEY,
			'order' => Product::SORT_ORDER . ' asc',
		];
		//
		parent::__construct($attributes);
	}

	public static function fromVariantId($id) {
		$variant = Variant::with(Product::TABLE)->find($id);
		if (is_null($variant)) {
			return null;
		}
		return new static([
			'slug' => $variant->slug,
			'title' => $variant->{Product::TABLE}->title . ': ' . $variant->title,
			'variant_id' => intval($variant->{Variant::KEY}),
			'product_id' => intval($variant->product_id),
			'amount' => 0,
			'units' => $variant->{Product::TABLE}->units,
			'cost' => doubleval($variant->cost),
		]);
	}

	public function getProductIdOptions() {
		$products = Product::all(['title', Product::KEY])->lists('title', Product::KEY);
		if ($this->exists) {
			$find = explode(': ', $this->title, 2);
			$products[$this->product_id] = $find[0];
		}
		return $products;
	}

	public function getVariantIdOptions() {
		$variants = Variant::where('product_id', '=', $this->product_id)->get(['title', Variant::KEY])->lists('title', Variant::KEY);
		if ($this->exists) {
			$find = explode(': ', $this->title, 2);
			if (count($find) < 2) {
				$find[1] = trans('rbin.shop::lang.forms.base');
			}
			$variants[$this->variant_id] = $find[1];
		}
		return $variants;
	}

	public function filterFields($fields) {
		$variant = Variant::find($this->variant_id, ['cost']);
		if (!is_null($variant)) {
			$fields->cost->value = $variant->cost;
		}
	}

	public function scopeTopVariant(Builder $query) {
		return $query->groupBy('variant_id')->selectRaw('title, product_id, sum(amount) as sum')->orderBy('sum', 'desc')->first();
	}

	public function beforeValidate() {
		$variant = Variant::with(Product::TABLE)->find($this->variant_id);
		if (!is_null($variant)) {
			$this->title = $variant->{Product::TABLE}->title . ': ' . $variant->title;
		}
	}

}