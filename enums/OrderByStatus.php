<?php namespace RBIn\Shop\Enums;

use RBIn\Shop\Classes\Enum;

/**
 * Статус заказа.
 * @package RBIn\Shop\Enums
 */
abstract class OrderByStatus extends Enum {

	// Ожидает
	const expects = 'rbin.shop::lang.orders.status.expects';

	// Принят
	const accepted = 'rbin.shop::lang.orders.status.accepted';

	// Не удался
	const rejected = 'rbin.shop::lang.orders.status.rejected';

	// Выполнен
	const made = 'rbin.shop::lang.orders.status.made';

}