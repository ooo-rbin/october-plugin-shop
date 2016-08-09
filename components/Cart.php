<?php namespace RBIn\Shop\Components;

use October\Rain\Database\Relations\BelongsTo;
use Auth;
use RBIn\Shop\Classes\Component;
use Cms\Classes\Page;
use RBIn\Shop\Models\Customer;
use RBIn\Shop\Models\Delivery;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Models\Option;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\OrderedVariant;
use RBIn\Shop\Models\Payment;
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
	public $options;
	public $customer;
	public $order;
	public $customer_info = '';

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
				'default'     => 'store-order',
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
		$variant = $this->cart->{OrderedVariant::TABLE}->where('variant_id', $id)->first();
		if (!is_null($variant)) {
			if ($amount > 0) {
				$variant->amount = $amount;
				$this->save();
				return [
					'icon' => 'check',
					'message' => trans('rbin.shop::lang.frontend.cart.changed'),
					$this->summary => $this->renderPartial('@summary'),
					'selector' => $this->informer,
					$this->informer => $this->renderPartial('@informer'),
				];
			} else {
				$variant_keys = $this->cart->{OrderedVariant::TABLE}->where('variant_id', $id)->keys();
				foreach ($variant_keys as $key) {
					$this->cart->{OrderedVariant::TABLE}->forget($key);
				}
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
		$variant_keys = $this->cart->{OrderedVariant::TABLE}->where('variant_id', $id)->keys();
		if (!empty($variant_keys)) {
			foreach ($variant_keys as $key) {
				$this->cart->{OrderedVariant::TABLE}->forget($key);
			}
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
		$variant = $this->cart->{OrderedVariant::TABLE}->where('variant_id', $id)->first();
		if (is_null($variant)) {
			$variant = OrderedVariant::fromVariantId($id);
			if (is_null($variant)) {
				throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.miss'));
			}
			$this->cart->{OrderedVariant::TABLE}->push($variant);
		}
		$variant->amount += 1;
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
		$this->customer = Auth::getUser();
		$this->cart = new Order(json_decode(Session::get($this->session, '[]'), true));
		if (isset($this->customer)) {
			$this->cart->customer_id = $this->customer->{Customer::KEY};
		} else {
			$this->cart->customer_id = null;
		}
		$this->save();
		$this->options = Option::whereIn('product_id', $this->cart->{OrderedVariant::TABLE}->lists('product_id'))->orderBy(Option::SORT_ORDER)->with([Feature::TABLE => function (BelongsTo $query) {
			return $query->where('show', '=', 1)->orderBy(Feature::SORT_ORDER);
		}])->get()->groupBy('product_id');
	}

	public function onChangeDelivery() {
		$delivery = $this->cart->validDeliveries->where(Delivery::KEY, intval(Input::get('delivery_id', 0)))->first();
		if (isset($delivery)) {
			$this->cart->delivery_id = $delivery->{Delivery::KEY};
		}
		$this->save();
		return [
			'icon' => 'check',
			'message' => trans('rbin.shop::lang.frontend.cart.changed'),
			$this->summary => $this->renderPartial('@summary'),
			'selector' => $this->informer,
			$this->informer => $this->renderPartial('@informer'),
		];
	}

	public function onChangePayment() {
		$payment = $this->cart->validPayments->where(Payment::KEY, intval(Input::get('payment_id', 0)))->first();
		if (isset($payment)) {
			$this->cart->payment_id = $payment->{Payment::KEY};
		}
		$this->save();
		return [
			'icon' => 'check',
			'message' => trans('rbin.shop::lang.frontend.cart.changed'),
			$this->summary => $this->renderPartial('@summary'),
			'selector' => $this->informer,
			$this->informer => $this->renderPartial('@informer'),
		];
	}

	protected function save() {
		$this->cart->callRules();
		Session::set($this->session, $this->cart->toJson(JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}

	public function onCreateOrder() {
		if (is_null($this->cart->{OrderedVariant::TABLE}->isEmpty())) {
			throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.empty'));
		}
		if (is_null($this->customer)) {
			throw new ApplicationException(trans('rbin.shop::lang.frontend.cart.guest'));
		}
		$this->cart->customer_info = Input::get('customer_info', '');
		$this->cart->save();
		Session::set($this->session, '[]');
		return Redirect::to($this->controller->pageUrl($this->order, [
			'slug' => $this->cart->slug,
		]));
	}

}