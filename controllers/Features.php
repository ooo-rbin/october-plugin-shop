<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Traits\ListController;
use RBIn\Shop\Traits\FormController;
use RBIn\Shop\Models\Category;
use RBIn\Shop\Models\CategorizedFeatures;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Traits\ReorderController;
use RBIn\Shop\Traits\ForeignNames;
use RBIn\Shop\Traits\SwitchingController;
use DB;
use October\Rain\Database\Builder;

class Features extends Controller {

	use ListController;
	use FormController {
		FormController::create as traitCreate;
	}
	use ReorderController {
		ReorderController::reorder as traitReorder;
	}
	use SwitchingController;

	public function __construct() {
		$this->bootListController();
		$this->bootFormController();
		$this->bootReorderController();
		parent::__construct();
	}

	protected $category_id;

	public function reorder($category_id = null) {
		$this->category_id = $category_id;
		$this->traitReorder();
	}

	use ForeignNames;

	public function reorderExtendQuery(Builder $query) {
		if (isset($this->category_id)) {
			$categoriesIndexName = $this->getForeignNames(Category::class)['column'];
			$featuresIndexName = $this->getForeignNames(Feature::class)['column'];
			$categoriesColumnName = implode('.', [CategorizedFeatures::TABLE, $categoriesIndexName]);
			$categorizedFeaturesColumnName = implode('.', [CategorizedFeatures::TABLE, $featuresIndexName]);
			$featuresColumnName = implode('.', [Feature::TABLE, Feature::KEY]);
			$query
				->leftJoin(CategorizedFeatures::TABLE, $categorizedFeaturesColumnName, '=', $featuresColumnName)
				->where($categoriesColumnName, '=', $this->category_id)
				->groupBy($featuresColumnName)
				->select(implode('.', [Feature::TABLE, '*']));
		} else {
			$query->where(Feature::KEY, '=', -1);
		}
	}

	public function getCategoriesList() {
		static $list;
		if (is_null($list)) {
			$list = [];
			foreach (DB::table(Category::TABLE)->select(Category::KEY, 'title')->get() as $item) {
				$list[$item->{Category::KEY}] = e($item->title);
			}
		}
		return $list;
	}

}