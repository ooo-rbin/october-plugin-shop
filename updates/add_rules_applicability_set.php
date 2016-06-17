<?php namespace RBIn\Shop\Updates;

use October\Rain\Database\Updates\Migration;
use RBIn\Shop\Traits\SetMigration;
use RBIn\Shop\Models\Rule;
use RBIn\Shop\Enums\RuleByApplicability;

/**
 * Добавление набора допустимых источников для правил заказа.
 * @package RBIn\Shop\Updates
 */
class AddRulesApplicabilitySet extends Migration {

	use SetMigration;

	/**
	 * Обновление таблицы.
	 */
	public function up() {
		$this->down();
		$this->createSet(Rule::TABLE, RuleByApplicability::class);
	}

	/**
	 * Откат таблицы.
	 */
	public function down() {
		$this->dropSet(Rule::TABLE, RuleByApplicability::class);
	}

}