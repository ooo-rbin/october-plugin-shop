<?php namespace RBIn\Shop\Controllers;

use RBIn\Shop\Classes\Controller;
use RBIn\Shop\Traits\IndexController;

class Rules extends Controller {

	use IndexController;

	protected $requiredPermissions = [
		'rbin.shop.rules.*',
	];

}