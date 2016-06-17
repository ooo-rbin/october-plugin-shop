<?php namespace RBIn\Shop\Traits;

use Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Логика генерации вспомогательных таблиц связи многие ко многим.
 * @package RBIn\Shop\Updates
 */
trait RelationMigration {

	/**
	 * Создает таблицу связи многие ко многим.
	 * @param string $fromModelClass Класс модели эммитора
	 * @param string $toModelClass Класс модели коллектора
	 * @param string|boolean $relationTable Имя используемой таблицы
	 * @param integer $ibfk Порядковый индекс внешнего ограничения
	 */
	protected function createMoreToMoreRelation($fromModelClass, $toModelClass, $relationTable = false, $ibfk = 1) {
		$relationTables = [
			$fromModelClass::TABLE => $fromModelClass,
			$toModelClass::TABLE => $toModelClass,
		];
		ksort($relationTables);
		if (!$relationTable) {
			$relationTable = implode('_', array_keys($relationTables));
		}
		if (Schema::hasTable($relationTable)) {
			$action = 'table';
		} else {
			$action = 'create';
		}
		Schema::$action($relationTable, function (Blueprint $table) use ($relationTable, &$relationTables, $ibfk) {
			$table->engine = 'InnoDB';
			$columnNames = [];
			foreach ($relationTables as $tableName => $modelClass) {
				$keyName = $modelClass::KEY;
				$indexName = str_replace('\\', '', snake_case(class_basename($modelClass)));
				$columnName = implode('_', [$indexName, $keyName]);
				$table->unsignedInteger($columnName);
				$table->foreign($columnName, "${relationTable}_ibfk_${ibfk}")->references($keyName)->on($tableName)->onUpdate('cascade')->onDelete('cascade');
				$columnNames[] = $columnName;
				$ibfk++;
			}
			$table->unique($columnNames, "unique_${relationTable}");
		});
	}

	/**
	 * Уничтожает таблицу связи многие ко многим.
	 * @param string $fromModelClass Класс модели эммитора
	 * @param string $toModelClass Класс модели коллектора
	 * @param string|boolean $relationTable Имя используемой таблицы
	 * @param integer $ibfk Порядковый индекс внешнего ограничения
	 */
	protected function dropMoreToMoreRelation($fromModelClass, $toModelClass, $relationTable = false, $ibfk = 1) {
		$relationTables = [
			$fromModelClass::TABLE => $fromModelClass,
			$toModelClass::TABLE => $toModelClass,
		];
		ksort($relationTables);
		if (!$relationTable) {
			$relationTable = implode('_', array_keys($relationTables));
		}
		if (Schema::hasTable($relationTable)) {
			Schema::table($relationTable, function (Blueprint $table) use ($relationTable, &$relationTables, $ibfk) {
				$columnNames = [];
				foreach ($relationTables as $tableName => $modelClass) {
					$keyName = $modelClass::KEY;
					$indexName = str_replace('\\', '', snake_case(class_basename($modelClass)));
					$columnName = implode('_', [$indexName, $keyName]);
					$table->dropForeign("${relationTable}_ibfk_${ibfk}");
					$columnNames[] = $columnName;
					$ibfk++;
				}
				$table->dropUnique("unique_${relationTable}");
				if ((count(Schema::getColumnListing($relationTable))) > 2) {
					foreach ($columnNames as $columnName) {
						$table->dropColumn($columnName);
					}
				} else {
					$table->drop();
				}
			});
		}
	}

	/**
	 * Создает таблицу изоморфной связи многие ко многим.
	 * @param string $modelClass Класс модели эммитора
	 * @param string $morphName Название изоморфа
	 * @param string|boolean $relationTable Имя используемой таблицы
	 * @param integer $ibfk Порядковый индекс внешнего ограничения
	 */
	protected function createPolymorphicMoreToMoreRelation($modelClass, $morphName, $relationTable = false, $ibfk = 1) {
		if (!$relationTable) {
			$relationTable = str_plural($morphName);
		}
		if (Schema::hasTable($relationTable)) {
			$action = 'table';
		} else {
			$action = 'create';
		}
		Schema::$action($relationTable, function (Blueprint $table) use ($relationTable, $modelClass, $morphName, $ibfk) {
			$tableName = $modelClass::TABLE;
			$keyName = $modelClass::KEY;
			$indexName = str_replace('\\', '', snake_case(class_basename($modelClass)));
			$columnName = implode('_', [$indexName, $keyName]);
			$table->engine = 'InnoDB';
			// Столбцы
			$table->unsignedInteger($columnName);
			$table->unsignedInteger("{$morphName}_id");
			$table->string("{$morphName}_type");
			// Ключи
			$table->foreign($columnName, "${relationTable}_ibfk_${ibfk}")->references($keyName)->on($tableName)->onUpdate('cascade')->onDelete('cascade');
			$table->unique([$columnName, "{$morphName}_id", "{$morphName}_type"], "unique_${morphName}");
		});
	}

	/**
	 * Уничтожает таблицу изоморфной связи многие ко многим.
	 * @param string $modelClass Класс модели эммитора
	 * @param string $morphName Название изоморфа
	 * @param string|boolean $relationTable Имя используемой таблицы
	 * @param integer $ibfk Порядковый индекс внешнего ограничения
	 */
	protected function dropPolymorphicMoreToMoreRelation($modelClass, $morphName, $relationTable = false, $ibfk = 1) {
		if (!$relationTable) {
			$relationTable = str_plural($morphName);
		}
		if (Schema::hasTable($relationTable)) {
			Schema::table($relationTable, function (Blueprint $table) use ($relationTable, $modelClass, $morphName, $ibfk) {
				$keyName = $modelClass::KEY;
				$indexName = str_replace('\\', '', snake_case(class_basename($modelClass)));
				$columnName = implode('_', [$indexName, $keyName]);
				$table->dropForeign("${relationTable}_ibfk_${ibfk}");
				$table->dropUnique("unique_${morphName}");
				if ((count(Schema::getColumnListing($relationTable))) > 3) {
					$table->dropColumn($columnName);
					$table->dropColumn("{$morphName}_id");
					$table->dropColumn("{$morphName}_type");
				} else {
					$table->drop();
				}
			});
		}
	}

}