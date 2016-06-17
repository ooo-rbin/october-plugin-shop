<?php namespace RBIn\Shop\Classes;

use Backend\Models\ExportModel as BaseExportModel;
use RBIn\Shop\Traits\Model as ModelTrait;
use RBIn\Shop\Traits\CurrentModel;

abstract class ExportModel extends BaseExportModel {

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

	public function exportData($columns, $sessionKey = null) {
		$class = $this->getCurrentModel();
		$models = $class::all();
		$models->each(function(Model $model) use ($columns) {
			$model->addVisible($columns);
		});
		return $models->toArray();
	}

}