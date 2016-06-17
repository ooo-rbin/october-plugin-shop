<?php namespace RBIn\Shop\Traits;

trait ForeignNames {

	protected function getForeignNames($class) {
		$indexName = str_replace('\\', '', snake_case(class_basename($class)));
		$columnName = implode('_', [$indexName, $class::KEY]);
		return [
			'index' =>$indexName,
			'column' => $columnName
		];
	}

	protected function getJoinName($classFrom, $classTo = null) {
		return $this->getColumnName($classFrom, (is_null($classTo)) ? $classFrom::KEY : $this->getForeignNames($classTo)['column']);
	}

	protected function getColumnName($class, $column) {
		return join('.', [$class::TABLE, $column]);
	}

	protected function getColumnNames($class, array $columns) {
		return array_map(function ($column) use ($class) {
			return $this->getColumnName($class, $column);
		}, $columns);
	}

	protected function getSortName($class, $column = 'order', $sort = 'asc') {
		$sort = trim($sort);
		return (in_array($sort, ['asc', 'desc'])) ? $this->getColumnName($class, $column) . " ${sort}" : $this->getColumnName($class, $column);
	}

}