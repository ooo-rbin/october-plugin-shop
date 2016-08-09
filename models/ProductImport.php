<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\ImportModel;

class ProductImport extends ImportModel {

	const TABLE = Product::TABLE;

	public function getCategoriesOptions() {
		return Category::all(['title', Category::KEY])->lists('title', Category::KEY);
	}

	protected function getCategories() {
		static $categories;
		if (is_null($categories)) {
			$categories = implode(', ', Category::whereIn(Category::KEY, $this->categories)->get(['title'])->list('title'));
		}
		return $categories;
	}

	protected $modelKey = 'title';

	protected function prepareData(array $results) {
		if (empty($results['categories'])) {
			$results['categories'] = $this->getCategories();
		}
		if (empty($results['variant_balance'])) {
			$results['variant_balance'] = ($this->balance == 'toNull') ? null : 0;
		}
		if (empty($results['variant_cost'])) {
			$results['variant_cost'] = ($this->cost == 'toNull') ? null : 0;
		}
		if (empty($results['variant_title'])) {
			$results['variant_title'] = $this->variant;
		}
		if (empty($results['variant_slug'])) {
			$results['variant_slug'] = str_slug($this->variant);
		}
		if (empty($results['show'])) {
			$results['show'] = $this->show;
		}
		return $results;
	}

}