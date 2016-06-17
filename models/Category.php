<?php namespace RBIn\Shop\Models;

use Str;
use October\Rain\Database\Traits\NestedTree;
use System\Models\File;
use RBIn\Shop\Classes\Model;

/**
 * Категория каталога продукции.
 * @package RBIn\Shop\Models
 */
class Category extends Model {

	const TABLE = 'rbin_shop_categories';

	use NestedTree;

	const PARENT_ID = 'category_id';
	const NEST_LEFT = 'nest_left';
	const NEST_RIGHT = 'nest_right';
	const NEST_DEPTH = 'nest_depth';

	public function beforeValidate() {
		if (empty($this->slug)) {
			$this->slug = Str::slug($this->title);
		}
		$this->show = intval($this->show);
	}

	public function __construct(array $attributes = []) {
		// Правила
		$this->rules = [
			'slug' => 'required|string|between:1,255|unique:' . static::TABLE,
			'show' => 'boolean',
			'title' => 'required|string|between:1,255|unique:' . static::TABLE,
			'annotation' => 'string',
			'description' => 'string',
			'keywords' => 'string|between:1,255',
			'meta_title' => 'string|between:1,255',
			'meta_description' => 'string|between:1,255',
			static::PARENT_ID => 'exists:' . static::TABLE. ',' . static::KEY,
			static::NEST_LEFT => 'integer',
			static::NEST_RIGHT => 'integer',
			static::NEST_DEPTH => 'integer',
		];
		// Связи
		$this->attachOne['picture'] = [
			File::class
		];
		$this->belongsToMany[Product::TABLE] = [
			Product::class,
			'table' => CategorizedProduct::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Product::class)['column'],
			'order' => Product::SORT_ORDER . ' asc',
		];
		$this->belongsToMany[Feature::TABLE] = [
			Feature::class,
			'table' => CategorizedFeatures::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Feature::class)['column'],
			'order' => Feature::SORT_ORDER . ' asc',
		];
		//$this->morphMany[Rule::TABLE] = [
		//	Rule::class,
		//	'name' => 'from',
		//	'order' => Rule::SORT_ORDER . ' asc',
		//];
		//
		parent::__construct($attributes);
	}

	public function getProductsCountAttribute() {
		return $this->{Product::TABLE}()->count();
	}

}