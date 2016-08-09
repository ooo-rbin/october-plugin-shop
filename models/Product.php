<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use System\Models\File;
use October\Rain\Database\Traits\Sortable;
use Str;
use DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Продукт в каталоге.
 * @package RBIn\Shop\Models
 */
class Product extends Model {

	const TABLE = 'rbin_shop_products';

	use Sortable;

	const SORT_ORDER = 'order';

	public function getSortOrderAttribute() {
		return $this->{static::SORT_ORDER};
	}

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
			'units' => 'string',
			'annotation' => 'string',
			'description' => 'string',
			'keywords' => 'string|between:1,255',
			'meta_title' => 'string|between:1,255',
			'meta_description' => 'string|between:1,255',
			static::SORT_ORDER => 'integer',
		];
		// Связи
		$this->attachOne['picture'] = [
			File::class
		];
		$this->hasMany[Variant::TABLE] = [
			Variant::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
			'order' => $this->getSortName(Variant::class),
		];
		$this->hasMany[Option::TABLE] = [
			Option::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
			'order' => $this->getSortName(Option::class),
		];
		$this->belongsToMany[Feature::TABLE] = [
			Feature::class,
			'table' => Option::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Feature::class)['column'],
			'order' => $this->getSortName(Feature::class),
		];
		$this->belongsToMany[Category::TABLE] = [
			Category::class,
			'table' => CategorizedProduct::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Category::class)['column'],
			'order' => $this->getSortName(Category::class, Category::NEST_LEFT),
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

	protected $fillable = [
		'slug',
		'show',
		'title',
		'units',
		'annotation',
		'description',
		'keywords',
		'meta_title',
		'meta_description',
		'categories',
		'variant_slug',
		'variant_title',
		'variant_balance',
		'variant_cost',
		'features',
	];

	protected $categories = null;

	public function getCategoriesAttribute() {
		return (isset($this->categories)) ? $this->categories : $this->{Category::TABLE}()->get(['title'])->implode('title', ', ');
	}

	public function setCategoriesAttribute($categories) {
		$this->categories = array_filter(array_map('trim', explode(',', $categories)));
	}

	protected $variant_slug = null;
	protected $variant_title = null;
	protected $variant_balance = null;
	protected $variant_cost = null;

	public function setVariantSlugAttribute($variant_slug) {
		$this->variant_slug = trim($variant_slug);
	}

	public function getVariantSlugAttribute() {
		return $this->variant_slug;
	}

	public function setVariantTitleAttribute($variant_title) {
		$this->variant_title = trim($variant_title);
	}

	public function getVariantTitleAttribute() {
		return $this->variant_title;
	}

	public function setVariantBalanceAttribute($variant_balance) {
		if (is_null($variant_balance)) {
			$this->variant_balance = null;
		} else {
			$this->variant_balance = intval($variant_balance);
		}
	}

	public function getVariantBalanceAttribute() {
		return $this->variant_balance;
	}

	public function setVariantCostAttribute($variant_cost) {
		if (is_null($variant_cost)) {
			$this->variant_cost = null;
		} else {
			$this->variant_cost = floatval($variant_cost);
		}
	}

	public function getVariantCostAttribute() {
		return $this->variant_cost;
	}

	protected $features = null;

	public function setFeaturesAttribute(array $features) {
		$this->features = $features;
	}

	public function getFeaturesAttribute() {
		return (isset($this->features)) ? $this->features : $this->{Option::TABLE}()->get(['value', Option::KEY])->lists('value', Option::KEY);
	}

	public function afterSave() {
		if (isset($this->categories)) {
			$this->{Category::TABLE}()->sync(array_filter(array_map(function ($category) {
				$category = Category::where('title', 'like', $category)->orWhere('slug', 'like', $category)->first([Category::KEY]);
				return (is_null($category)) ? $category : $category->{Category::KEY};
			}, $this->categories)));
		}
		$this->categories = null;
		if (isset($this->variant_slug)) {
			$variant = $this->{Variant::TABLE}()->where('slug', 'like', $this->variant_slug)->first();
			if (is_null($variant)) {
				$variant = new Variant();
				$variant->slug = $this->variant_slug;
			}
			$variant->title = $this->variant_title;
			$variant->balance = $this->variant_balance;
			$variant->cost = $this->variant_cost;
			$this->{Variant::TABLE}()->save($variant);
		}
		$this->variant_slug = null;
		$this->variant_title = null;
		$this->variant_balance = null;
		$this->variant_cost = null;
		if (isset($this->features)) {
			$featureId = $this->getForeignNames(Feature::class)['column'];
			foreach ($this->features as $id => $value) {
				$option = $this->{Option::TABLE}()->where($featureId, '=', $id)->first();
				if (is_null($option)) {
					$option = new Option();
					$option->$featureId = $id;
					$option->value = $value;
					$this->{Option::TABLE}()->save($option);
				} else {
					$option->value = $value;
					$option->save();
				}
			}
		}
		$this->features = null;
	}

	public function getOptionsAttribute() {
		$feature_id = $this->getJoinName(Feature::class);
		$option_featureId = $this->getJoinName(Option::class, Feature::class);
		$feature_title = $this->getColumnName(Feature::class, 'title');
		$option_value = $this->getColumnName(Option::class, 'value');
		return $this->{Option::TABLE}()->join(Feature::TABLE, $feature_id, '=', $option_featureId)->get([$feature_title, $option_value])->toArray();
	}

	public function setOptionsAttribute($value) {
		$featureId = $this->getForeignNames(Feature::class)['column'];
		$options = $this->{Option::TABLE}->keyBy($featureId)->toArray();
		foreach ($value as $order => $value) {
			$feature = Feature::where('title', 'like', trim($value['title']))->first();
			if (is_null($feature)) {
				$feature = new Feature();
				$feature->title = trim($value['title']);
				$feature->order = intval($order);
				$feature->save();
				$option = new Option();
				$option->$featureId = $feature->{Feature::KEY};
				$option->value = trim($value['value']);
				$option->order = intval($order);
				$this->{Option::TABLE}()->save($option);
			} else {
				$option = $this->{Option::TABLE}()->where($featureId, '=', $feature->{Feature::KEY})->first();
				if (is_null($option)) {
					$option = new Option();
					$option->$featureId = $feature->{Feature::KEY};
					$option->value = trim($value['value']);
					$option->order = intval($order);
					$this->{Option::TABLE}()->save($option);
				} else {
					$option->value = trim($value['value']);
					$option->order = intval($order);
					$option->update();
					unset($options[$feature->id]);
				}
			}
		}
		$this->{Option::TABLE}()->whereIn($featureId, array_keys($options))->delete();
	}

	public function scopeFilters(Builder $query, $filters) {
		if (!is_array($filters)) {
			$filters = [$filters];
		}
		$features = [];
		foreach ($filters as $filter) {
			$find = [];
			preg_match('/filter\[([^]]*)\]\[([^]]*)\]/', $filter, $find);
			$find[1] = intval($find[1]);
			if (!isset($features[$find[1]])) {
				$features[$find[1]] = [];
			}
			$features[$find[1]][] = $find[2];
		}
		$ids = [];
		$productId = $this->getForeignNames(static::class)['column'];
		foreach ($features as $filter => $values) {
			$ids = array_merge($ids, Option::where($this->getForeignNames(Feature::class)['column'], '=', $filter)->where(function (Builder $query) use ($values) {
				foreach ($values as $value) {
					$query->orWhere('value', 'like', $value);
				}
				return $query;
			})->get([$productId, Option::KEY])->lists($productId, Option::KEY));
		}
		$query->whereIn(static::KEY, $ids);
		return $query;
	}

	public function scopeListFrontEnd(Builder $query, $options) {
		$page = 1;
		$pagination = 10;
		$sort = static::SORT_ORDER;
		$category = null;
		$search = '';
		$published = true;
		$filters = [];
		extract($options, EXTR_IF_EXISTS);
		$searchableFields = ['title', 'slug', 'keywords', 'meta_title', 'meta_description'];
		$products_show = $this->getColumnName(static::class, 'show');
		if ($published) {
			$query->where($products_show, '=', 1);
		}
		if (!is_array($sort)) {
			$sort = [$sort];
		}
		foreach ($sort as $_sort) {
			$parts = explode(' ', $_sort);
			if (count($parts) < 2) {
				array_push($parts, 'asc');
			}
			list($sortField, $sortDirection) = $parts;
			$query->orderBy($this->getColumnName(static::class, $sortField), $sortDirection);
		}
		$search = trim($search);
		if (strlen($search)) {
			$query->where(function (Builder $query) use ($search, $searchableFields) {
				foreach ($searchableFields as $field) {
					$query->orWhere($field, 'like', $search);
				}
				return $query;
			});
		}
		$product_id = $this->getJoinName(static::class);
		$cp_categoryId = $this->getJoinName(CategorizedProduct::class, Category::class);
		$cp_productId = $this->getJoinName(CategorizedProduct::class, static::class);
		if (!empty($filters['categories']) && is_array($filters['categories'])) {
			$categories = $filters['categories'];
			$query->leftJoin(CategorizedProduct::TABLE, $cp_productId, '=', $product_id);
			$query->whereIn($cp_categoryId, $categories);
		} elseif ($category !== null) {
			$category = Category::find($category);
			$categories = $category->getAllChildrenAndSelf()->lists('id');
			$query->leftJoin(CategorizedProduct::TABLE, $cp_productId, '=', $product_id);
			$query->whereIn($cp_categoryId, $categories);
		}
		$options_productId = $this->getJoinName(Option::class, static::class);
		$query->leftJoin(Option::TABLE, $options_productId, '=', $product_id);
		$query->where(function (Builder $query) use ($filters) {
			$options_featureId = $this->getJoinName(Option::class, Feature::class);
			$options_value = $this->getColumnName(Option::class, 'value');
			foreach ($filters as $filter => $restriction) {
				if ($filter != 'products' && $filter != 'categories') {
					$query->where(function (Builder $query) use ($filter, $restriction, $options_featureId, $options_value) {
						foreach ($restriction as $value) {
							$query->orWhere(function (Builder $query) use ($filter, $value, $options_featureId, $options_value) {
								return $query->where($options_featureId, '=', intval($filter))->where($options_value, 'like', $value);
							});
						}
					});
				}
			}
			return $query;
		});
		if (!empty($filters['products']) && is_array($filters['products'])) {
			$products = $filters['products'];
			$query->orWhere(function (Builder $query) use ($products_show, $published, $product_id, $products) {
				if ($published) {
					$query->where($products_show, '=', 1);
				}
				$query->whereIn($product_id, $products);
				return $query;
			});
		}
		$query->groupBy($product_id);
		$query->select($this->getColumnName(static::class, '*'));
		return $query->paginate($pagination, $page);
	}

	public function getValues() {
		$featureId = $this->getForeignNames(Feature::class)['column'];
		return $this->{Option::TABLE}()->get(['value', $featureId])->lists('value', $featureId);
	}

	public function getVariants() {
		$variant_id = $this->getJoinName(Variant::class);
		return $this->{Variant::TABLE}()->get(['title', 'balance', 'cost', $variant_id]);
	}

	public function addVisible($attributes = null) {
		if (is_array($attributes)) {
			if (in_array('categories', $attributes)) {
				$this->attributes['categories'] = $this->getCategoriesAttribute();
			}
		}
		return parent::addVisible($attributes);
	}

}