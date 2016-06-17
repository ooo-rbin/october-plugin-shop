<?php namespace RBIn\Shop\Components;

use RBIn\Shop\Classes\Component;
use RBIn\Shop\Models\Order as OrderModel;

class Order extends Component {

	public function componentDetails() {
		return [
			'name' => 'rbin.shop::lang.frontend.order.name',
			'description' => 'rbin.shop::lang.frontend.order.description'
		];
	}

	public function defineProperties() {
		return [
			'slug' => [
				'title' => 'rbin.shop::lang.frontend.slug.title',
				'description' => 'rbin.shop::lang.frontend.slug.description',
				'type' => 'string',
				'default' => '{{ :slug }}',
			],
		];
	}

	public $slugParam;
	public $slug;
	public $order;

	public function onRun()	{
		$this->prepareVars();
	}

	protected function prepareVars() {
		$this->slugParam = $this->paramName('slug');
		$this->slug = $this->property($this->slugParam, false);
		$this->order = OrderModel::singleFrontEnd([
			'slug' => $this->slug,
		])->first();
	}

}