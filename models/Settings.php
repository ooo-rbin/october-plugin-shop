<?php namespace RBIn\Shop\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use ValidationException;

/**
 * Настройки магазина.
 * @package RBIn\Shop\Models
 */
class Settings extends Model {

	use Validation;

	public $rules = [];

	protected $jsonable = [
		'requisites',
	];

	public $implement = ['System.Behaviors.SettingsModel'];

	public $settingsCode = 'rbin_shop_settings';

	public $settingsFields = 'fields.yaml';

	public function beforeValidate() {
		$requisites = $this->requisites;
		foreach ($requisites as &$requisite) {
			$requisite['rule'] = strval($requisite['rule']);
			$requisite['title'] = strval($requisite['title']);
			$requisite['comment'] = strval($requisite['comment']);
			if (!empty($requisite['rule']) && @preg_match('/' . $requisite['rule'] . '/', null) === false) {
				throw new ValidationException([
					'requisites' => trans('rbin.shop::lang.settings.requisites.regexp'),
				]);
			}
			if (empty($requisite['title'])) {
				throw new ValidationException([
					'requisites' => trans('rbin.shop::lang.settings.requisites.require_title'),
				]);
			}
			if (empty($requisite['code'])) {
				throw new ValidationException([
					'code' => trans('rbin.shop::lang.settings.requisites.require_code'),
				]);
			}
			foreach ($requisite as $value) {
				if (mb_strlen($value, 'UTF-8') > 255) {
					throw new ValidationException([
						'requisites' => trans('rbin.shop::lang.settings.requisites.len'),
					]);
				}
			}
		}
		$this->requisites = $requisites;
	}

}