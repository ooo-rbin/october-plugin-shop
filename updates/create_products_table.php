<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Product;

/**
 * Миграция продукции.
 * @package RBIn\Shop\Updates
 */
class CreateProductsTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Product::TABLE, function (Blueprint $table) {
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Product::KEY, 'primary');
			$table->string('slug');
			$table->boolean('show');
			$table->boolean('featured');
			$table->string('title');
			$table->text('annotation');
			$table->text('description');
			$table->string('keywords');
			$table->string('meta_title');
			$table->string('meta_description');
			$table->integer('order')->nullable();
			// Ключи
			$table->unique('slug', 'unique_product');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		Schema::dropIfExists(Product::TABLE);
	}

}
