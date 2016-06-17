<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Traits\ReorderController;
use October\Rain\Database\Builder;
use ApplicationException;
use BackendMenu;

class Variants extends Controller {

	use ReorderController {
		ReorderController::reorder as traitReorder;
	}

	public function __construct() {
		$this->bootReorderController();
		parent::__construct();
		BackendMenu::setContext('RBIn.Shop', 'rbin_shop', str_replace('\\', '', snake_case(class_basename(Products::class))));
	}

	protected $product;

	public function reorder($product_id = null) {
		$this->product = Product::find(intval($product_id));
		if (is_null($this->product)) {
			throw new ApplicationException(trans(''));
		}
		$this->traitReorder();
	}

	public function reorderExtendQuery(Builder $query) {
		$productIndexName = str_replace('\\', '', snake_case(class_basename(Product::class)));
		$productColumnName = implode('_', [$productIndexName, Product::KEY]);
		$query
			->where($productColumnName, '=', $this->product->{Product::KEY})
			->groupBy(Product::KEY);
	}

}