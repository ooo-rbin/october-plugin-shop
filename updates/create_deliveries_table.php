<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Delivery;

/**
 * Миграция способов доставки.
 * @package RBIn\Shop\Updates
 */
class CreateDeliveriesTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Delivery::TABLE, function (Blueprint $table) {
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Delivery::KEY, 'primary');
			$table->boolean('show');
			$table->string('title');
			$table->text('description');
			$table->decimal('cost');
			$table->boolean('separately');
			$table->integer('order')->nullable();
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		Schema::dropIfExists(Delivery::TABLE);
	}

}
