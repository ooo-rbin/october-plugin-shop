<?php namespace RBIn\Shop\Behaviors;

use Backend\Behaviors\ImportExportController;

class CsvController extends ImportExportController {

	public function extendCsvListColumns(array $columns) {
		return $columns;
	}

	protected function makeListColumns($config) {
		return $this->controller->extendCsvListColumns(parent::makeListColumns($config));
	}

}