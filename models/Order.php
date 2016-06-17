<?php namespace RBIn\Shop\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Message;
use RBIn\Shop\Classes\Model;
use RainLab\User\Models\User;
use RBIn\Shop\Enums\OrderByPayment;
use RBIn\Shop\Enums\OrderByStatus;
use October\Rain\Database\Traits\SoftDeleting;
use Auth;
use Mail;
use Backend\Models\User as Admin;
use Markdown;
use RainLab\Blog\Classes\TagProcessor;
use System\Models\File;

/**
 * Заказ по каталогу продукции.
 * @package RBIn\Shop\Models
 */
class Order extends Model {

	use SoftDeleting;

	protected $dates = ['deleted_at'];

	const TABLE = 'rbin_shop_orders';

	public $timestamps = true;

	protected $fillable = [
		'total_cost',
	];

	public function __construct(array $attributes = []) {
		// Правила
		$this->rules = [
		];
		// Связи
		$this->belongsTo[Customer::TABLE] = [
			User::class,
			'key' => $this->getForeignNames(Customer::class)['column'],
			'otherKey' => Customer::KEY,
		];
		$this->belongsTo[Delivery::TABLE] = [
			Delivery::class,
			'key' => $this->getForeignNames(Delivery::class)['column'],
			'otherKey' => Delivery::KEY,
			'order' => Delivery::SORT_ORDER . ' asc',
		];
		$this->belongsTo[Payment::TABLE] = [
			Payment::class,
			'key' => $this->getForeignNames(Payment::class)['column'],
			'otherKey' => Payment::KEY,
			'order' => Payment::SORT_ORDER . ' asc',
		];
		$this->hasMany[OrderedVariant::TABLE] = [
			OrderedVariant::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
		];
		$this->hasMany[OrderedRule::TABLE] = [
			OrderedRule::class,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => static::KEY,
		];
		$this->belongsToMany[Variant::TABLE] = [
			Variant::class,
			'table' => OrderedVariant::TABLE,
			'key' => $this->getForeignNames(static::class)['column'],
			'otherKey' => $this->getForeignNames(Variant::class)['column'],
			'order' => Variant::SORT_ORDER . ' asc',
		];
		$this->attachMany['rbin_shop_order_files'] = [
			File::class,
			'order' => 'sort_order',
		];
		//$this->belongsToMany[Rule::TABLE] = [
		//	Rule::class,
		//	'table' => OrderedRule::TABLE,
		//	'key' => $this->getForeignNames(static::class)['column'],
		//	'otherKey' => $this->getForeignNames(Rule::class)['column'],
		//	'order' => Variant::SORT_ORDER . ' asc',
		//];
		//
		parent::__construct($attributes);
	}

	public function trans($column, $value = null) {
		if (is_null($value)) {
			$value = $this->$column;
		}
		switch ($column) {
			case 'status':
				return OrderByStatus::trans($value);
			case 'payment':
				return OrderByPayment::trans($value);
		}
		return false;
	}

	public function scopeByStatus(Builder $query, $statuses = []) {
		if (!is_array($statuses)) {
			$statuses = [$statuses];
		}
		return (empty($statuses)) ? $query : $query->where(function (Builder $query) use ($statuses) {
			foreach ($statuses as $status) {
				$query = $query->orWhere('status', '=', $status);
			}
			return $query;
		});
	}

	public function scopeByPayment(Builder $query, $payments = []) {
		if (!is_array($payments)) {
			$payments = [$payments];
		}
		return (empty($payments)) ? $query : $query->where(function (Builder $query) use ($payments) {
			foreach ($payments as $payment) {
				$query = $query->orWhere('status', '=', $payment);
			}
			return $query;
		});
	}

	public function scopeTrashed(Builder $query) {
		$query->onlyTrashed();
	}

	public function getStatusOptions() {
		return OrderByStatus::getConstants(true);
	}

	public function getPaymentOptions() {
		return OrderByPayment::getConstants(true);
	}

	public function getCustomerIdOptions() {
		return array_map(function ($user) {
			return "${user['name']} [${user['email']}]";
		}, User::all(['id', 'name', 'email'])->keyBy('id')->toArray());
	}

	protected $message = '';

	public function setMessageAttribute($message) {
		$this->message = strval($message);
	}

	public function getMessageAttribute() {
		return $this->message;
	}

	protected $recall = false;

	public function setRecallAttribute($recall) {
		$this->recall = boolval($recall);
	}

	public function getRecallAttribute() {
		return $this->recall;
	}

	protected $remind = true;

	public function setRemindAttribute($remind) {
		$this->remind = boolval($remind);
	}

	public function getRemindAttribute() {
		return $this->remind;
	}

	public function getPaymentIdOptions() {
		$payments = Payment::all(['title', Payment::KEY])->lists('title', Payment::KEY);
		return ($this->exists) ? array_merge($payments, [$this->payment_id => $this->payment_title]) : $payments;
	}

	public function getDeliveryIdOptions() {
		$deliveries = Delivery::all(['title', Delivery::KEY])->lists('title', Delivery::KEY);
		return ($this->exists) ? array_merge($deliveries, [$this->delivery_id => $this->delivery_title]) : $deliveries;
	}

	public function scopeTopCustomer(Builder $query) {
		$user_id = $this->getJoinName(Customer::class);
		$orders_customerId = $this->getJoinName(static::class, Customer::class);
		$orders_totalPayment = $this->getColumnName(static::class, 'total_payment');
		$user_name = $this->getColumnName(Customer::class, 'name');
		$user_email = $this->getColumnName(Customer::class, 'email');
		return $query
			->join(Customer::TABLE, $user_id, '=', $orders_customerId)
			->groupBy($user_id)
			->selectRaw("${user_id}, ${user_name}, ${user_email}, sum(${orders_totalPayment}) as sum")
			->orderBy('sum', 'desc')
			->first();
	}

	public function scopeListFrontEnd(Builder $query, array $options = []) {
		$pagination = 10;
		extract($options, EXTR_IF_EXISTS);
		$customer = Auth::getUser();
		if (is_null($customer)) {
			$customerId = 0;
		} else {
			$customerId = intval($customer->{Customer::KEY});
		}
		return $query
			->where($this->getForeignNames(Customer::class)['column'], '=', $customerId)
			->orderBy(static::CREATED_AT, 'desk')
			->with(OrderedVariant::TABLE)
			->paginate($pagination);
	}

	public function scopeSingleFrontEnd(Builder $query, array $options = []) {
		$slug = '';
		extract($options, EXTR_IF_EXISTS);
		$customer = Auth::getUser();
		if (is_null($customer)) {
			$customerId = 0;
		} else {
			$customerId = intval($customer->{Customer::KEY});
		}
		return $query
			->where($this->getForeignNames(Customer::class)['column'], '=', $customerId)
			->where('slug', 'like', $slug)
			->with(OrderedVariant::TABLE);
	}

	public function beforeValidate() {
		if (empty($this->payment_id)) {
			$this->payment_id = null;
		}
		if (empty($this->delivery_id)) {
			$this->delivery_id = null;
		}
		if ($this->recall) {
			$this->total_cost = array_reduce($this->{OrderedVariant::TABLE}()->get(['amount', 'cost'])->toArray(), function ($cost, $variant) {
				return $cost + ($variant['amount'] * $variant['cost']);
			}, 0); // TODO учитывать стоимость доставки и оплаты
		}
		if (!empty($this->message) || $this->remind) {
			if (empty($this->message)) {
				$message = trans('rbin.shop::lang.orders.remind_message');
			} else {
				$message = $this->message;
			}
			// TODO настройки писем
			$requisites = [];
			foreach (Settings::get('requisites', []) as $requisite) {
				$requisites[$requisite['code']] = $requisite;
			}
			foreach ($this->{Customer::TABLE}->{Requisite::TABLE} as $requisite) {
				if (isset($requisites[$requisite->code])) {
					$requisites[$requisite['code']]['value'] = $requisite->value;
				}
			}
			foreach ($this->{Customer::TABLE}->rbin_shop_requisite_files as $file) {
				$requisites[$file->getLocalPath()] = [
					'url' => url($file->getPath()),
					'value' => $file->file_name,
					'title' => $file->title,
				];
			}
			Mail::sendTo(array_merge($this->{Customer::TABLE}()->get(['email'])->lists('email'), Admin::all(['email'])->lists('email')), 'rbin.shop::mail.order', [
				'order' => $this,
				'text' => TagProcessor::instance()->processTags(Markdown::parse($message), false),
				'requisites' => $requisites,
				'url' => url('/cabinet/orders/' . $this->slug),
			], function (Message $message) {
				foreach ($this->rbin_shop_order_files as $file) {
					$message->attach($file->getLocalPath(), [
						'as' => $file->file_name,
						'type' => $file->content_type,
					]);
				}
			});
		}
	}

}