<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Feature;

/**
 * Миграция свойств продукции.
 * @package RBIn\Shop\Updates
 */
class CreateFeaturesTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Feature::TABLE, function (Blueprint $table) {
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Feature::KEY, 'primary');
			$table->unsignedInteger(Feature::SORT_ORDER);
			$table->boolean('show');
			$table->boolean('is_filter');
			$table->string('title');
			$table->text('description');
			// Ключи
			$table->unique('title', 'unique_feature');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		Schema::dropIfExists(Feature::TABLE);
	}

}
