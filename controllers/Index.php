<?php namespace RBIn\Shop\Controllers;

use Carbon\Carbon;
use Backend\Widgets\Form;
use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Enums\OrderByPayment;
use RBIn\Shop\Enums\OrderByStatus;
use RBIn\Shop\Classes\Email;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\OrderedVariant;
use RBIn\Shop\Traits\IndexController;
use Request;
use Input;

class Index extends Controller {

	use IndexController {
		IndexController::index as traitIndex;
	}

	protected $requiredPermissions = [
		'rbin.shop.*',
	];

	public function index() {
		if (Request::isMethod('post') && Input::get('action', '') == 'sendMail') {
			return $this->sendMail();
		}
		$this->traitIndex();
		$this->vars['ordersTotal'] = Order::count();
		$this->vars['ordersByStatus'] = [];
		foreach (OrderByStatus::getConstants(true) as $status => $title) {
			$this->vars['ordersByStatus'][$status] = [
				'title' => $title,
				'count' => Order::where('status', '=', $status)->count(),
			];
		}
		$this->vars['ordersByPayment'] = [];
		foreach (OrderByPayment::getConstants(true) as $payment => $title) {
			$this->vars['ordersByPayment'][$payment] = [
				'title' => $title,
				'count' => Order::where('payment', '=', $payment)->count(),
			];
		}
		$orders = Order::where(Order::CREATED_AT, '>=', Carbon::today()->modify('-6 month'))
			->get(['total_cost', 'total_payment', Order::CREATED_AT])
			->groupBy(function ($order) {
				return Carbon::parse($order->{Order::CREATED_AT})->format('Y/m/d');
			})->toArray();
		ksort($orders);
		$ordersInTime = array_map(function ($group, $date) {
			return array_reduce($group, function ($carry, $item) {
				$carry['total_cost'] += $item['total_cost'];
				$carry['total_payment'] += $item['total_payment'];
				return $carry;
			}, [
				'date' => Carbon::parse($date)->format('d.m.Y'),
				'total_cost' => 0,
				'total_payment' => 0
			]);
		}, $orders, array_keys($orders));
		while(($ordersInTimeCount = count($ordersInTime)) < 12) {
			array_unshift($ordersInTime, [
				'date' => Carbon::today()->modify("-${ordersInTimeCount} month")->format('d.m.Y'),
				'total_cost' => 0,
				'total_payment' => 0
			]);
		}
		$this->vars['ordersInTime'] = $ordersInTime;
		$this->vars['customer'] = Order::topCustomer();
		$this->vars['product'] = OrderedVariant::topVariant();
		$config = $this->makeConfig('$/rbin/shop/models/email/fields.yaml');
		$config->model = new Email();
		$config->context = 'index';
		$form = $this->makeWidget(Form::class, $config);
		$form->bindToController();
	}

}