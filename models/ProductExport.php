<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\ExportModel;

class ProductExport extends ExportModel {

	const TABLE = Product::TABLE;

	protected $names = [
		'products' => [
			'slug' => 'rbin.shop::lang.',
			'show' => 'rbin.shop::lang.',
			'title' => 'rbin.shop::lang.',
			'annotation' => 'rbin.shop::lang.',
			'description' => 'rbin.shop::lang.',
			'keywords' => 'rbin.shop::lang.',
			'meta_title' => 'rbin.shop::lang.',
			'meta_description' => 'rbin.shop::lang.',
			'categories' => 'rbin.shop::lang.',
		],
		'variants' => [
			'title' => 'rbin.shop::lang.',
			'balance' => 'rbin.shop::lang.',
			'cost' => 'rbin.shop::lang.',
		],
	];

	public function exportData($columns, $sessionKey = null) {
		$product_columns = [];
		$variant_columns = [];
		$features_id = [];
		foreach ($columns as $column) {
			$find = [];
			if (preg_match('/feature_([\d]+)/', $column, $find)) {
				$features_id[] = intval($find[1]);
			} else if (preg_match('/variant_([\S]+)/', $column, $find)) {
				$variant_columns[] = $find[1];
			} else {
				$product_columns[] = $column;
			}
		}
		$products = Product::with([
			Option::TABLE,
			Variant::TABLE,
		])->get();
		$variants = [];
		foreach ($products as $product) {
			$product->addVisible($product_columns);
			$product_attr = $product->toArray();
			foreach ($product->{Option::TABLE} as $option) {
				if (in_array(intval($option->feature_id), $features_id)) {
					$product_attr['feature_' . $option->feature_id] = $option->value;
				}
			}
			foreach ($product->{Variant::TABLE} as $variant) {
				$variant->addVisible($variant_columns);
				$attr = $product_attr;
				foreach ($variant->toArray() as $variant_attr => $value) {
					$attr['variant_'.$variant_attr] = $value;
				}
				$variants[] = $attr;
			}
		}
		return $variants;
	}

}