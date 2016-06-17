<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Payment;

/**
 * Миграция способов оплаты.
 * @package RBIn\Shop\Updates
 */
class CreatePaymentsTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Payment::TABLE, function (Blueprint $table) {
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Payment::KEY, 'primary');
			$table->boolean('show');
			$table->unsignedInteger('priority');
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
		Schema::dropIfExists(Payment::TABLE);
	}

}
