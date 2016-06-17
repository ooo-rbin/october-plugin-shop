<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Traits\ListController;
use RBIn\Shop\Traits\FormController;
use RBIn\Shop\Traits\ReorderController;
use RBIn\Shop\Traits\SwitchingController;

class Deliveries extends Controller {

	use ListController;
	use FormController;
	use ReorderController;
	use SwitchingController;

	public function __construct() {
		$this->bootListController();
		$this->bootFormController();
		$this->bootReorderController();
		parent::__construct();
	}

}