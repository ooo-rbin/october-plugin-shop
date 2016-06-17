<?php namespace RBIn\Shop\Components;

use RBIn\Shop\Classes\Component;
use RBIn\Shop\Models\Order as OrderModel;
use Cms\Classes\Page;

class Orders extends Component {

	public function componentDetails() {
		return [
			'name' => 'rbin.shop::lang.frontend.orders.name',
			'description' => 'rbin.shop::lang.frontend.orders.description'
		];
	}

	public function defineProperties() {
		return [
			'pagination' => [
				'title' => 'rbin.shop::lang.frontend.pagination.title',
				'description' => 'rbin.shop::lang.frontend.pagination.description',
				'type' => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rbin.shop::lang.frontend.pagination.validation',
				'default' => '10',
			],
			'order' => [
				'title'       => 'rbin.shop::lang.frontend.order.title',
				'description' => 'rbin.shop::lang.frontend.order.description',
				'type'        => 'dropdown',
				'default'     => 'store-order',
				'group'       => 'rbin.shop::lang.forms.links',
			],
		];
	}

	public function getOrderOptions() {
		return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
	}

	public $order;
	public $pagination;
	public $orders;

	public function onRun()	{
		$this->prepareVars();
	}

	protected function prepareVars() {
		$this->pagination = intval($this->property('pagination'));
		$this->order = 'cabinet-order';// $this->property('order');
		$this->orders = OrderModel::listFrontEnd([
			'pagination' => $this->pagination,
		]);
	}

	public function getUrl($slug) {
		return $this->controller->pageUrl($this->order, ['slug' => $slug]);
	}

}