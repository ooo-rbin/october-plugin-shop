<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;

/**
 * Абстракция пользователя.
 * @package RBIn\Shop\Models
 */
abstract class Customer extends Model {

	const TABLE = \RBIn\Shop\UserTable;
	const KEY = \RBIn\Shop\UserKey;

}
