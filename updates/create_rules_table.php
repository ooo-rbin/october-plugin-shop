<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Rule;

/**
 * Миграция правил заказов.
 * @package RBIn\Shop\Updates
 */
class CreateRulesTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Rule::TABLE, function (Blueprint $table) {
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Rule::KEY, 'primary');
			$table->boolean('show');
			$table->boolean('global');
			$table->string('title');
			$table->text('description');
			$table->text('value');
			$table->integer('order')->nullable();
			// Ключи
			$table->unique('title', 'unique_rule');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		Schema::dropIfExists(Rule::TABLE);
	}

}
