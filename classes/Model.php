<?php namespace RBIn\Shop\Classes;

use October\Rain\Database\Traits\Validation;
use Model as ModelBase;
use RBIn\Shop\Traits\Model as ModelTrait;

/**
 * Абстракция модели данных.
 * @package RBIn\Shop\Models
 */
abstract class Model extends ModelBase {

	const TABLE = null;
	const KEY = 'id';

	public function __construct(array $attributes = []) {
		$this->table = static::TABLE;
		$this->primaryKey = static::KEY;
		parent::__construct($attributes);
	}

	public $timestamps = false;
	protected $rules = [];

	use ModelTrait;
	use Validation;

}
