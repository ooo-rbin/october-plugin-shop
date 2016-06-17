<?php namespace RBIn\Shop\Classes;

use Exception;
use Backend\Models\ImportModel as BaseImportModel;
use RBIn\Shop\Traits\Model as ModelTrait;
use RBIn\Shop\Traits\CurrentModel;

abstract class ImportModel extends BaseImportModel {

	const TABLE = null;
	const KEY = 'id';

	public function __construct(array $attributes = []) {
		$this->table = static::TABLE;
		$this->primaryKey = static::KEY;
		parent::__construct($attributes);
	}

	public $timestamps = false;
	protected $rules = [];

	use CurrentModel;
	use ModelTrait;

	abstract protected function prepareData(array $results);

	protected $modelKey;

	protected function parse(array $data) {
		$result = [];
		foreach ($data as $key => $value) {
			$find = [];
			if (preg_match('/([^\[]*)\[([^\]]*)\]/', $key, $find)) {
				if (!isset($result[$find[1]])) {
					$result[$find[1]] = [];
				}
				$result[$find[1]][$find[2]] = $value;
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	public function importData($results, $sessionKey = null) {
		foreach ($results as $row => $data) {
			try {
				$class = $this->getCurrentModel();
				$options = $this->prepareData($this->parse($data));
				if (empty($options[$this->modelKey])) {
					throw new Exception(trans('rbin.shop::lang.forms.error_key'));
				}
				$model = $class::where($this->modelKey, 'like', $options[$this->modelKey])->first();
				if (is_null($model)) {
					$model = new $class();
				}
				$model->fill($options);
				$model->save();
				$this->logCreated();
			} catch (Exception $ex) {
				$this->logError($row, $ex->getMessage());
			}
		}
	}

}