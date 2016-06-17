<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Customer;
use RBIn\Shop\Models\Requisite;

/**
 * Миграция реквизитов заказчиков.
 * @package RBIn\Shop\Updates
 */
class CreateRequisitesTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Requisite::TABLE, function (Blueprint $table) {
			$requisitesColumnName = $this->getForeignNames(Customer::class)['column'];
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Requisite::KEY, 'primary');
			$table->unsignedInteger($requisitesColumnName);
			$table->string('code');
			$table->string('value');
			// Ключи
			$table->unique([$requisitesColumnName, 'code'], 'unique_requisite');
			$table->foreign($requisitesColumnName, Requisite::TABLE . '_ibfk_1')->references(Customer::KEY)->on(Customer::TABLE)->onUpdate('cascade')->onDelete('restrict');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(Requisite::TABLE)) {
			Schema::table(Requisite::TABLE, function (Blueprint $table) {
				// Ключи
				$table->dropForeign(Requisite::TABLE . '_ibfk_1');
			});
			Schema::drop(Requisite::TABLE);
		}
	}

}
