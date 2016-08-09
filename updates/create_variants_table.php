<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Models\Variant;
use RBIn\Shop\Traits\ForeignNames;

/**
 * Миграция вариантов товара.
 * @package RBIn\Shop\Updates
 */
class CreateVariantsTable extends Migration {

	use ForeignNames;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Variant::TABLE, function (Blueprint $table) {
			$productColumnName = $this->getForeignNames(Product::class)['column'];
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Variant::KEY, 'primary');
			$table->unsignedInteger($productColumnName);
			$table->string('slug');
			$table->string('title');
			$table->unsignedInteger('balance')->nullable();
			$table->decimal('cost')->nullable();
			$table->integer('order')->nullable();
			// Ключи
			$table->unique([$productColumnName, 'slug'], 'unique_variant');
			$table->foreign($productColumnName, Variant::TABLE . '_ibfk_1')->references(Product::KEY)->on(Product::TABLE)->onUpdate('cascade')->onDelete('cascade');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(Variant::TABLE)) {
			Schema::table(Variant::TABLE, function (Blueprint $table) {
				// Ключи
				$table->dropForeign(Variant::TABLE . '_ibfk_1');
			});
			Schema::drop(Variant::TABLE);
		}
	}

}
