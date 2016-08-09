<?php namespace RBIn\Shop\Updates;

use October\Rain\Database\Updates\Migration;
use RBIn\Shop\Models\Rule;
use RBIn\Shop\Models\RuledSource;
use RBIn\Shop\Traits\RelationMigration;

/**
 * Миграция связи правил заказов и их сточников.
 * @package RBIn\Shop\Updates
 */
class CreateRuledSourcesRelation extends Migration {

	use RelationMigration;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		$this->createPolymorphicMoreToMoreRelation(Rule::class, 'source', RuledSource::TABLE);
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		$this->dropPolymorphicMoreToMoreRelation(Rule::class, 'source', RuledSource::TABLE);
	}

}
