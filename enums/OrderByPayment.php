<?php namespace RBIn\Shop\Enums;

use RBIn\Shop\Classes\Enum;

/**
 * Статус оплаты заказа.
 * @package RBIn\Shop\Enums
 */
abstract class OrderByPayment extends Enum {

	// Оплачено
	const done = 'rbin.shop::lang.orders.payment.done';

	// Частично оплачено
	const partially = 'rbin.shop::lang.orders.payment.partially';

	// Переплачено
	const over_payment = 'rbin.shop::lang.orders.payment.overpayment';

	// Не оплачено
	const expects = 'rbin.shop::lang.orders.payment.expects';

}