<?php namespace RBIn\Shop\Models;

use RainLab\User\Models\User;
use RBIn\Shop\Classes\Model;

/**
 * Реквизиты клиента.
 * @package RBIn\Shop\Models
 */
class Requisite extends Model {

	const TABLE = 'rbin_shop_requisites';

	public function __construct(array $attributes = []) {
		// Связи
		$this->belongsTo[Customer::TABLE] = [
			User::class,
			'table' => Customer::TABLE,
			'key' => $this->getForeignNames(Customer::class)['column'],
			'otherKey' => Customer::KEY,
			'order' => User::UPDATED_AT . ' desc',
		];
		//
		parent::__construct($attributes);
	}

	public function getCodeOptions() {
		$result = [];
		foreach (Settings::get('requisites') as $requisite) {
			$result[$requisite['code']] = $requisite['title'];
		}
		return $result;
	}

	public function getCodeStrAttribute() {
		return $this->getCodeOptions()[$this->code];
	}

	public function getVariantsAttribute() {
		return static::query()->where('code', 'like', $this->code)->groupBy('value')->get(['value'])->lists('value');
	}

}