<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Traits\ListController;
use RBIn\Shop\Traits\CsvController;
use RBIn\Shop\Traits\FormController;
use RBIn\Shop\Traits\ReorderController;
use RBIn\Shop\Traits\RelationController;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Models\Category;
use RBIn\Shop\Models\CategorizedProduct;
use October\Rain\Database\Builder;
use RBIn\Shop\Traits\SwitchingController;
use Flash;

class Products extends Controller {

	use ListController;
	use CsvController;
	use FormController {
		FormController::create as traitCreate;
	}
	use RelationController;
	use ReorderController {
		ReorderController::reorder as traitReorder;
	}
	use SwitchingController;

	public function __construct() {
		$this->bootListController();
		$this->bootCsvController();
		$this->bootFormController();
		$this->bootReorderController();
		$this->bootRelationController();
		parent::__construct();
	}

	protected $category_id;

	public function reorder($category_id = null) {
		$this->category_id = $category_id;
		$this->traitReorder();
	}

	public function import() {
		Flash::info(trans('rbin.shop::lang.products.import_comment'));
		$this->asExtension('CsvController')->import();
	}

	public function reorderExtendQuery(Builder $query) {
		if (isset($this->category_id)) {
			$categoryIndexName = str_replace('\\', '', snake_case(class_basename(Category::class)));
			$categoryColumnName = implode('_', [$categoryIndexName, Category::KEY]);
			$query
				->leftJoin(CategorizedProduct::TABLE, $categoryColumnName, '=', Category::KEY)
				->where($categoryColumnName, '=', $this->category_id)
				->groupBy(Product::KEY);
		} else {
			$query->where(Product::KEY, '=', '0');
		}
	}

	public function extendCsvListColumns(array $columns) {
		$features = [];
		foreach (Feature::all([Feature::KEY, 'title']) as $feature) {
			$id = $feature->{Feature::KEY};
			$features["feature_${id}"] = $feature->title;
		}
		return array_merge($columns, $features);
	}

	public function makeVariantsPartial() {
		return $this->relationRender('rbin_shop_variants');
	}

	public function getCategories() {
		return Category::all(['title', Category::KEY]);
	}

	public function makeOptionsPartial() {
		return $this->relationRender('rbin_shop_options');
	}

	public function makeRulesPartial() {
		return $this->relationRender('rbin_shop_rules');
	}

}