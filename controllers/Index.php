<?php namespace RBIn\Shop\Controllers;

use Carbon\Carbon;
use Backend\Widgets\Form;
use Cms\Classes\MediaLibrary;
use Illuminate\Database\Query\Builder;
use October\Rain\Auth\Models\User;
use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Enums\OrderByPayment;
use RBIn\Shop\Enums\OrderByStatus;
use RBIn\Shop\Models\Email;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\OrderedVariant;
use RBIn\Shop\Traits\IndexController;
use Request;
use Input;
use Redirect;
use Flash;
use Mail;
use Markdown;
use RainLab\Blog\Classes\TagProcessor;
use Illuminate\Mail\Message;
use Config;

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
		while(($ordersInTimeCount = count($ordersInTime)) < 6) {
			array_unshift($ordersInTime, [
				'date' => Carbon::today()->modify("-${ordersInTimeCount} month")->format('d.m.Y'),
				'total_cost' => 0,
				'total_payment' => 0
			]);
		}
		$this->vars['ordersInTime'] = $ordersInTime;
		$this->vars['customer'] = Order::topCustomer()->toArray();
		$this->vars['product'] = OrderedVariant::topVariant()->toArray();
		$config = $this->makeConfig('$/rbin/shop/models/email/fields.yaml');
		$config->model = new Email();
		$config->context = 'index';
		$form = $this->makeWidget(Form::class, $config);
		$form->bindToController();
	}

	public function sendMail() {
		$group = intval(Input::get('recipients', 0));
		if ($group == 0) {
			$to = User::all(['email'])->lists('email');
		} else {
			$to = User::whereIn('id', function (Builder $query) use ($group) {
				$query->select('user_id')->from('users_groups')->where('user_group_id', $group);
			})->get(['email'])->lists('email');
		}
		if (empty($to)) {
			Flash::success(trans('rbin.shop::lang.index.message.nothing'));
		} else {
			Mail::sendTo($to, 'rbin.shop::mail.message', [
				'text' => TagProcessor::instance()->processTags(Markdown::parse(Input::get('message', '')), false),
			], function (Message $message) {
				$path = Config::get('filesystems.disks.local.root', storage_path()) . DIRECTORY_SEPARATOR . Config::get('cms.storage.media.folder', 'media');
				$message->subject(Input::get('subject', ''));
				foreach (Input::get('attachments', []) as $attachment) {
					$message->attach($path . DIRECTORY_SEPARATOR . $attachment['type']);
				}
			});
			Flash::success(trans('rbin.shop::lang.index.message.complete'));
		}
		return Redirect::refresh();
	}

}