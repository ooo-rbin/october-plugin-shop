<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Enums\OrderByPayment;
use RBIn\Shop\Enums\OrderByStatus;
use RBIn\Shop\Models\OrderedVariant;
use RBIn\Shop\Traits\ListController;
use RBIn\Shop\Traits\FormController;
use RBIn\Shop\Traits\RelationController;
use Illuminate\Database\Eloquent\Builder;

class Orders extends Controller {

	use ListController;
	use FormController;
	use RelationController;

	public function __construct() {
		$this->bootListController();
		$this->bootFormController();
		$this->bootRelationController();
		parent::__construct();
	}

	public function listExtendQuery(Builder $query)	{
		$q = $query->getQuery();
		if (is_array($q->orders)) {
			$orders = [];
			foreach ($q->orders as &$order) {
				if (isset($order['column'])) {
					if (!isset($order['direction'])) {
						$order['direction'] = 'desc';
					}
					switch ($order['column']) {
						case 'status':
							$orders[] = [OrderByStatus::class, $order['column'], $order['direction']];
							$order = null;
							break;
						case 'payment':
							$orders[] = [OrderByPayment::class, $order['column'], $order['direction']];
							$order = null;
							break;
					}
				}
			}
			$q->orders = array_filter($q->orders);
			$query->setQuery($q);
			foreach ($orders as $order) {
				list($enum, $column, $direction) = $order;
				$query = $enum::orderBy($query, $column, $direction);
			}
		}
		return $query;
	}

	public function makeProductsPartial() {
		return $this->relationRender(OrderedVariant::TABLE);
	}

	public function formExtendQuery($query) {
		$query->withTrashed();
	}

}