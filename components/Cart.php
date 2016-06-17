<?php namespace RBIn\Shop\Components;

use October\Rain\Database\Relations\BelongsTo;
use Auth;
use RBIn\Shop\Classes\Component;
use Cms\Classes\Page;
use RBIn\Shop\Models\Customer;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Models\Option;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\OrderedVariant;
use Session;
use Input;
use Redirect;
use ApplicationException;

class Cart extends Component {

	public $summary;
	public $informer;
	public $page;
	public $url;
	public $session;
	public $cart;
	public $totalCost;
	public $options;
	public $customer;
	public $order;
	public $message = '';

	public function componentDetails() {
		return [
			'name' => 'rbin.shop::lang.frontend.cart.name',
			'description' => 'rbin.shop::lang.frontend.cart.description'
		];
	}

	public function defineProperties() {
		return [
			'summary' => [
				'title'       => 'rbin.shop::lang.frontend.cart.summary.title',
				'description' => 'rbin.shop::lang.frontend.cart.summary.description',
				'type'        => 'string',
				'default'     => '#cart-summary',
			],
			'informer' => [
				'title'       => 'rbin.shop::lang.frontend.cart.informer.title',
				'description' => 'rbin.shop::lang.frontend.cart.informer.description',
				'type'        => 'string',
				'default'     => '#cart-informer',
			],
			'session' => [
				'title'       => 'rbin.shop::lang.frontend.cart.session.title',
				'description' => 'rbin.shop::lang.frontend.cart.session.description',
				'type'        => 'string',
				'default'     => 'rbin.shop.cart',
			],
			'page' => [
				'title'       => 'rbin.shop::lang.frontend.cart.page.title',
				'description' => 'rbin.shop::lang.frontend.cart.page.description',
				'type'        => 'dropdown',
				'default'     => 'store-cart',
				'group'       => 'rbin.shop::lang.forms.links',
			],
			'order' => [
				'title'       => 'rbin.shop::lang.frontend.cart.page.title',
				'description' => 'rbin.shop::lang.frontend.cart.page.description',
				'type'        => 'dropdown',
				'default'     => 'store-cart',
				'group'       => 'rbin.shop::lang.forms.links',
			],
		];
	}

	public function getPageOptions() {
		return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
	}

	public function getOrderOptions() {
		return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
	}

	public function onRun()	{
		$this->prepareVars();
		$this->addJs('assets/cart.js', 'core');
	}

	public function runAjaxHandler($handler) {
		$this->prepareVars();
		return parent::runAjaxHandler($handler);
	}

	public function onChangeAmountInCart() {
		$id = intval(Input::get('variant'));
		$amount = intval(Input::get('amount'));
		if ($this->cart->has($id)) {
			$this->cart[$id]->amount = $amount;
			if ($this->cart[$id]->amount > 0) {
				$this->save();
				return [
					$this->summary => $this->renderPartial('@summary'),
				];
			} else {
				$this->cart->forget($id);
				$this->save();
				return [
					'icon' => 'check',
					'message' => trans('rbin.shop::lang.frontend.cart.removed'),
					$this->summary => $this->renderPartial('@summary'),
					'selector' => $this->informer,
					$this->informer => $this->renderPartial('@informer'),
				];
			}
		} else {
			throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.miss'));
		}
	}

	public function onRemoveFromCart() {
		$id = intval(Input::get('variant'));
		if ($this->cart->has($id)) {
			$this->cart->forget($id);
			$this->save();
			return [
				'icon' => 'check',
				'message' => trans('rbin.shop::lang.frontend.cart.removed'),
				$this->summary => $this->renderPartial('@summary'),
				'selector' => $this->informer,
				$this->informer => $this->renderPartial('@informer'),
			];
		} else {
			throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.miss'));
		}
	}

	public function onAddToCart() {
		$id = intval(Input::get('variant'));
		if (!$this->cart->has($id)) {
			$variant = OrderedVariant::fromVariantId($id);
			if (is_null($variant)) {
				throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.miss'));
			}
			$this->cart->put($variant->variant_id, $variant);
		}
		$this->cart[$id]->amount += 1;
		$this->save();
		return [
			'icon' => 'check',
			'message' => trans('rbin.shop::lang.frontend.cart.append'),
			'selector' => $this->informer,
			$this->informer => $this->renderPartial('@informer'),
		];
	}

	public function onChartInformer() {
		return [
			'content' => $this->renderPartial('@informer'),
			'selector' => $this->informer,
		];
	}

	protected function prepareVars() {
		$this->summary = $this->property('summary');
		$this->informer = $this->property('informer');
		$this->session = $this->property('session');
		$this->page = $this->property('page');
		$this->order = $this->property('order');
		$this->url = $this->controller->pageUrl($this->page);
		$this->cart = collect(array_map(function ($data) {
			return new OrderedVariant((array) $data);
		}, json_decode(Session::get($this->session, '[]'), true)));
		$this->save();
		$this->customer = Auth::getUser();
	}

	protected function save() {
		$this->cart = $this->cart->keyBy('variant_id');
		Session::set($this->session, $this->cart->toJson(JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		$this->calc();
		$this->options = Option::whereIn('product_id', $this->cart->lists('product_id'))->orderBy(Option::SORT_ORDER)->with([Feature::TABLE => function (BelongsTo $query) {
			return $query->where('show', '=', 1)->orderBy(Feature::SORT_ORDER);
		}])->get()->groupBy('product_id');
	}

	protected function calc() {
		$this->totalCost = $this->cart->reduce(function ($carry, $item) {
			return $carry + ($item->amount * $item->cost);
		}, 0);
	}

	public function onCreateOrder() {

		if (is_null($this->cart->isEmpty())) {
			throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.empty'));
		}
		if (is_null($this->customer)) {
			throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.guest'));
		}
		$order = new Order();
		$order->slug = uniqid();
		$order->status = 'expects';
		$order->payment = 'expects';
		$order->customer_info = strval(Input::get('message'));
		$order->customer_id = $this->customer->{Customer::KEY};
		$order->payment_id = intval(Input::get('payment'));
		$order->delivery_id = intval(Input::get('delivery'));
		$order->message = $this->message;
		$order->recall = true;
		$order->save();
		$order->{OrderedVariant::TABLE}()->saveMany($this->cart->all());
		// TODO send emails
		Session::set($this->session, '[]');
		return Redirect::to($this->controller->pageUrl($this->order, [
			'slug' => $order->slug,
		]));
	}

}