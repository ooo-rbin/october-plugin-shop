<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Вариант продукта.
 * @package RBIn\Shop\Models
 */
class Feature extends Model {

	const TABLE = 'rbin_shop_features';

	use Sortable;

	const SORT_ORDER = 'order';

	public function getSortOrderAttribute() {
		return $this->{static::SORT_ORDER};
	}

	public function __construct(array $attributes = []) {
		// Правила
		$this->jsonable[] = 'options';
		$this->rules = [
		];
		// Связи
		$this->belongsToMany[Category::TABLE] = [
			Category::class,
			'table' => CategorizedFeatures::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Category::class)['column'],
			'order' => Category::NEST_LEFT . ' asc',
		];
		$this->belongsToMany[Product::TABLE] = [
			Product::class,
			'table' => Option::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Product::class)['column'],
			'order' => Product::SORT_ORDER . ' asc',
		];
		$this->hasMany[Option::TABLE] = [
			Option::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
			'order' => Option::SORT_ORDER . ' asc',
		];
		//$this->morphMany[Rule::TABLE] = [
		//	Rule::class,
		//	'name' => 'from',
		//	'order' => Rule::SORT_ORDER . ' asc',
		//];
		//
		parent::__construct($attributes);
	}

	public function scopeCategories(Builder $query, $category_id = 0) {
		if (is_array($category_id)) {
			$query = $query->whereIn($this->getJoinName(CategorizedFeatures::class, Category::class), $category_id);
		} else {
			$query = $query->where($this->getJoinName(CategorizedFeatures::class, Category::class), '=', intval($category_id));
		}
		return $query
			->leftJoin(CategorizedFeatures::TABLE, $this->getJoinName(CategorizedFeatures::class, static::class), '=', $this->getJoinName(static::class))
			->groupBy($this->getJoinName(static::class));
	}

}