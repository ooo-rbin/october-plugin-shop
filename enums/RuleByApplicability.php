<?php namespace RBIn\Shop\Enums;

use RBIn\Shop\Classes\Enum;
use RBIn\Shop\Models\Category;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Models\Variant;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Models\Option;
use RBIn\Shop\Models\Customer;
use RBIn\Shop\Models\Delivery;
use RBIn\Shop\Models\Payment;

/**
 * Статус заказа.
 * @package RBIn\Shop\Enums
 */
abstract class RuleByApplicability extends Enum {

	const category = Category::class;
	const product  = Product::class;
	const variant  = Variant::class;
	const feature  = Feature::class;
	const customer = Customer::class;
	const delivery = Delivery::class;
	const payment  = Payment::class;

}