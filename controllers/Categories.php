<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Traits\ListController;
use RBIn\Shop\Traits\FormController;
use RBIn\Shop\Traits\ReorderController;
use RBIn\Shop\Traits\RelationController;
use RBIn\Shop\Traits\SwitchingController;

class Categories extends Controller {

	use ListController;
	use FormController;
	use ReorderController;
	use RelationController;
	use SwitchingController;

	public function __construct() {
		$this->bootListController();
		$this->bootFormController();
		$this->bootReorderController();
		$this->bootRelationController();
		parent::__construct();
	}

	public function makeProductsPartial() {
		return $this->relationRender('rbin_shop_products');
	}

	public function makeFeaturesPartial() {
		return $this->relationRender('rbin_shop_features');
	}

}