<?php namespace RBIn\Shop\Traits;

use Schema;
use DB;
use Illuminate\Database\Schema\Blueprint;

trait SetMigration {

	protected function createSet($relationTable, $setClass) {
		$setName = preg_replace('/^.*?_by_/', '', str_replace('\\', '', snake_case(class_basename($setClass))));
		$set = str_replace('\\', '\\\\', implode('\',\'', array_keys($setClass::getConstants())));
		if (Schema::hasTable($relationTable)) {
			$action = 'ALTER';
			if (Schema::hasColumn($relationTable, $setName)) {
				$subAction = 'CHANGE';
			} else {
				$subAction = 'ADD';
			}
		} else {
			$action = 'CREATE';
			$subAction = 'ADD';
		}
		DB::statement("${action} TABLE `${relationTable}` ${subAction} `$setName` SET('${set}') NOT NULL");
	}

	protected function dropSet($relationTable, $setClass) {
		$setName = preg_replace('/^.*?_by_/', '', str_replace('\\', '', snake_case(class_basename($setClass))));
		if (Schema::hasTable($relationTable)) {
			if (Schema::hasColumn($relationTable, $setName)) {
				Schema::table($relationTable, function(Blueprint $table) use ($setName) {
					$table->dropColumn($setName);
				});
			}
		}
	}

}