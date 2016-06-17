<?php namespace RBIn\Shop\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Заказы.
 * @package RBIn\Shop\Models
 */
class Cart extends Model {

	use Validation;

	public $rules = [];

	protected $guarded = [];

	protected $fillable = ['*'];

}