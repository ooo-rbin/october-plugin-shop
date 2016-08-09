<?php namespace RBIn\Shop;

use Backend;
use Event;
use Illuminate\Events\Dispatcher;
use RainLab\User\Controllers\Users;
use RBIn\Shop\Controllers\Orders;
use RBIn\Shop\Enums\OrderByPayment;
use RBIn\Shop\Enums\OrderByStatus;
use RBIn\Shop\Models\Requisite;
use RBIn\Shop\Models\Settings;
use System\Classes\PluginBase;
use RainLab\User\Models\User;
use Backend\Widgets\Filter;
use RBIn\Shop\Controllers\Products;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Models\Option;
use RBIn\Shop\Models\Customer;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\Rule;
use RBIn\Shop\Models\RuledSource;
use RBIn\Shop\Components\Cart as CartComponent;
use RBIn\Shop\Components\Products as ProductsComponent;
use RBIn\Shop\Components\Orders as OrdersComponent;
use RBIn\Shop\Components\Order as OrderComponent;
use RBIn\Shop\Components\Requisites as RequisitesComponent;
use Backend\Widgets\Form;
use RBIn\Shop\Traits\PluginIntegrator;
use System\Models\File;

require_once 'constants.php';

/**
 * Информация о плагине
 */
class Plugin extends PluginBase {

	use PluginIntegrator;

	/**
	 * @var array Зависимости плагина.
	 */
	public $require = [
		'RainLab.User',
	];

	/**
	 * Регистрация информации о плагине.
	 * @return array
	 */
	public function pluginDetails() {
		return [
			'name' => 'rbin.shop::lang.name',
			'description' => 'rbin.shop::lang.description',
			'author' => 'rbin.shop::lang.author',
			'icon' => 'icon-shopping-cart',
			'homepage' => 'http://github.com/ooo-rbin/october-shop-plugin',
		];
	}

	/**
	 * Регистрация прав доступа.
	 * @return array
	 */
	public function registerPermissions() {
		return [
			///////////////////////////////////////////////////////////////////////////////////////////////// Настройки
			// Способы доставки
			'rbin.shop.deliveries.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.deliveries.label',
			],
			// Способы оплаты
			'rbin.shop.payments.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.payments.label',
			],
			// Настройки
			'rbin.shop.settings.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.settings.label',
			],
			////////////////////////////////////////////////////////////////////////////////////////////// Заказ online
			// Правила заказов
			'rbin.shop.rules.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.rules.label',
			],
			// Заказы
			'rbin.shop.orders.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.orders.label',
			],
			///////////////////////////////////////////////////////////////////////////////////////// Каталог продукции
			// Категории
			'rbin.shop.categories.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.categories.label',
			],
			// Продукты
			'rbin.shop.products.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.products.label',
			],
			'rbin.shop.products.import' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.products.import',
			],
			'rbin.shop.products.export' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.products.export',
			],
			// Свойства продуктов
			'rbin.shop.features.access' => [
				'tab' => 'rbin.shop::lang.name',
				'label' => 'rbin.shop::lang.features.label',
			],
		];
	}

	public function registerSettings() {
		return [
			'rbin_shop_settings' => [
				'label' => 'rbin.shop::lang.name',
				'description' => 'rbin.shop::lang.settings.description',
				'category' => 'system::lang.system.categories.cms',
				'icon' => 'icon-shopping-cart',
				'class' => Settings::class,
				'order' => 500,
				'keywords' => 'rbin.shop::lang.settings.keywords',
				'permissions' => ['rbin.shop.settings.*'],
			],
		];
	}

	public function registerNavigation() {
		return [
			'rbin_shop' => [
				'label' => 'rbin.shop::lang.name',
				'url' => Backend::url('rbin/shop'),
				'icon' => 'icon-shopping-cart',
				'permissions' => ['rbin.shop.*'],
				'order' => 500,
				'sideMenu' => [
					'index' => [
						'label' => 'rbin.shop::lang.index.label',
						'icon' => 'icon-heartbeat',
						'url' => Backend::url('rbin/shop'),
						'permissions' => ['rbin.shop.*'],
						'attributes' => ['data-class' => 'latest-in-group'],
					],
					'orders' => [
						'label' => 'rbin.shop::lang.orders.label',
						'icon' => 'icon-cart-arrow-down',
						'url' => Backend::url('rbin/shop/orders'),
						'permissions' => ['rbin.shop.orders.*'],
						'attributes' => ['data-class' => 'latest-in-group'],
						'counter' => function () {
							return Order::where('status', '=', 'expects')->count();
						},
						'counterLabel' => 'rbin.shop::lang.orders.counter',
					],
					'categories' => [
						'label' => 'rbin.shop::lang.categories.label',
						'icon' => 'icon-cubes',
						'url' => Backend::url('rbin/shop/categories'),
						'permissions' => ['rbin.shop.categories.*'],
					],
					'products' => [
						'label' => 'rbin.shop::lang.products.label',
						'icon' => 'icon-cube',
						'url' => Backend::url('rbin/shop/products'),
						'permissions' => ['rbin.shop.products.*'],
					],
					'features' => [
						'label' => 'rbin.shop::lang.features.label',
						'icon' => 'icon-filter',
						'url' => Backend::url('rbin/shop/features'),
						'permissions' => ['rbin.shop.features.*'],
						'attributes' => ['data-class' => 'latest-in-group'],
					],
					'deliveries' => [
						'label' => 'rbin.shop::lang.deliveries.label',
						'icon' => 'icon-truck',
						'url' => Backend::url('rbin/shop/deliveries'),
						'permissions' => ['rbin.shop.deliveries.*'],
					],
					'payments' => [
						'label' => 'rbin.shop::lang.payments.label',
						'icon' => 'icon-money',
						'url' => Backend::url('rbin/shop/payments'),
						'permissions' => ['rbin.shop.payments.*'],
						'attributes' => ['data-class' => 'latest-in-group'],
					],
					'rules' => [
						'label' => 'rbin.shop::lang.rules.label',
						'icon' => 'icon-cogs',
						'url' => Backend::url('rbin/shop/rules'),
						'permissions' => ['rbin.shop.rules.*'],
					],
					'settings' => [
						'label' => 'rbin.shop::lang.settings.label',
						'icon' => 'icon-wrench',
						'url' => Backend::url('system/settings/update/rbin/shop/rbin_shop_settings'),
						'permissions' => ['rbin.shop.settings'],
					],
				],
			],
		];
	}

	public function registerComponents() {
		return [
			// Корзина
			CartComponent::class => CartComponent::getComponentName(),
			// Продукция
			ProductsComponent::class => ProductsComponent::getComponentName(),
			// Заказы
			OrdersComponent::class => OrdersComponent::getComponentName(),
			// Заказ
			OrderComponent::class => OrderComponent::getComponentName(),
			// Реквизиты
			RequisitesComponent::class => RequisitesComponent::getComponentName(),
		];
	}

	public function registerMailTemplates() {
		return [
			'rbin.shop::mail.order' => trans('rbin.shop::lang.orders.mail'),
		];
	}

	/**
	 * Обработка события запуска плагина.
	 */
	public function boot() {
		$this->extendClass(User::class);
		$this->extendClass(Users::class);
		Event::subscribe($this);
	}

	public function subscribe(Dispatcher $events) {
		$events->listen('backend.form.extendFields', $this->getHandler('extendUsersFields'));
		$events->listen('backend.filter.extendScopes', $this->getHandler('extendProductsFilters'));
		$events->listen('backend.filter.extendScopes', $this->getHandler('extendOrdersFilters'));
	}

	protected function extendUser(User $model) {
		$customerIndexName = str_replace('\\', '', snake_case(class_basename(Customer::class)));
		$customerColumnName = implode('_', [$customerIndexName, Customer::KEY]);
		$ruleIndexName = str_replace('\\', '', snake_case(class_basename(Rule::class)));
		$ruleColumnName = implode('_', [$ruleIndexName, Rule::KEY]);
		$model->hasMany[Order::TABLE] = [
			Order::class,
			'key' => $customerColumnName,
			'otherKey' => Customer::KEY,
			'order' => Order::CREATED_AT,
		];
		$model->hasMany[Requisite::TABLE] = [
			Requisite::class,
			'key' => $customerColumnName,
			'otherKey' => Customer::KEY,
		];
		$model->attachMany['rbin_shop_requisite_files'] = [
			File::class,
			'order' => 'sort_order',
		];
		$model->morphToMany[Rule::TABLE] = [
			Rule::class,
			'scope' => 'isApplied',
			'table' => RuledSource::TABLE,
			'otherKey' => $ruleColumnName,
			'name' => 'source',
			'order' => implode(' ', [Rule::SORT_ORDER, 'asc']),
		];
	}

	protected function extendUsers(Users $controller) {
		$controller->addDynamicProperty('relationConfig');
		$controller->relationConfig = '$/rbin/shop/models/customer/relation.yaml';
		$controller->implement[] = 'Backend.Behaviors.RelationController';
	}

	protected function extendUsersFields(Form $widget) {
		if ($widget->getController() instanceof Users && $widget->model instanceof User) {
			$widget->addFields([
				Requisite::TABLE => [
					'tab' => 'rbin.shop::lang.settings.requisites.name',
					'type' => 'partial',
					'path' => '$/rbin/shop/models/customer/_requisites.htm'
				],
			], 'primary');
			$widget->addFields([
				'rbin_shop_requisite_files' => [
					'tab' => 'rbin.shop::lang.settings.requisites.name',
					'label' => 'rbin.shop::lang.settings.requisites.files',
					'type' => 'fileupload',
					'mode' => 'file',
					'useCaption' => true,
					'prompt' => 'rbin.shop::lang.forms.add',
				],
			], 'primary');
			$widget->addFields([
				Rule::TABLE => [
					//'tab' => 'rbin.shop::lang.settings.requisites.name',
					'label' => 'rbin.shop::lang.rules.label',
					'commentAbove' => 'rbin.shop::lang.rules.comment',
					'type' => 'relation',
					'nameFrom' => 'title',
					'descriptionFrom' => 'description',
					'emptyOption' => 'rbin.shop::lang.forms.empty',
				],
			], 'secondary');
		}
	}

	protected function extendProductsFilters(Filter $widget) {
		if ($widget->getController() instanceof Products) {
			$features = Feature::where('is_filter', true)->orderBy(Feature::SORT_ORDER, 'asc')->select(['id', 'title'])->get();
			foreach ($features as $feature) {
				$options = $feature->{Option::TABLE}()->groupBy('value')->get(['value', Option::KEY])->lists('value', Option::KEY);
				if (!empty($options)) {
					$variants = [];
					foreach ($options as $value) {
						$variants["filter[{$feature->id}][${value}]"] = $value;
					}
					$widget->addScopes([
						$feature->title => [
							'label' => $feature->title,
							'options' => $variants,
							'scope' => 'filters',
						],
					]);
				}
			}
		}
	}

	protected function extendOrdersFilters(Filter $widget) {
		if ($widget->getController() instanceof Orders) {
			$widget->addScopes([
				'status' => [
					'label' => 'rbin.shop::lang.orders.status.label',
					'options' => OrderByStatus::getConstants(true),
					'scope' => 'byStatus',
				],
			]);
			$widget->addScopes([
				'payment' => [
					'label' => 'rbin.shop::lang.orders.payment.label',
					'options' => OrderByPayment::getConstants(true),
					'scope' => 'byPayment',
				],
			]);
			$widget->addScopes([
				'customer_id' => [
					'label' => 'rbin.shop::lang.orders.customer',
					'options' => array_map(function ($user) {
						return "${user['name']} [${user['email']}]";
					}, User::all(['id', 'name', 'email'])->keyBy('id')->toArray()),
					'conditions' => 'customer_id in (:filtered)',
				],
			]);
		}
	}

}
