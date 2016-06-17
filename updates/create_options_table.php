<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Option;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Traits\ForeignNames;

/**
 * Миграция значений свойств вариантов продукции.
 * @package RBIn\Shop\Updates
 */
class CreateOptionsTable extends Migration {

	use ForeignNames;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Option::TABLE, function (Blueprint $table) {
			$featureColumnName = $this->getForeignNames(Feature::class)['column'];
			$productColumnName = $this->getForeignNames(Product::class)['column'];
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Option::KEY, 'primary');
			$table->string('value');
			$table->unsignedInteger($featureColumnName);
			$table->unsignedInteger($productColumnName);
			$table->unsignedInteger(Option::SORT_ORDER);
			// Ключи
			$table->unique([$featureColumnName, $productColumnName], 'unique_option');
			$table->foreign($featureColumnName, Option::TABLE . '_ibfk_1')->references(Feature::KEY)->on(Feature::TABLE)->onUpdate('cascade')->onDelete('cascade');
			$table->foreign($productColumnName, Option::TABLE . '_ibfk_2')->references(Product::KEY)->on(Product::TABLE)->onUpdate('cascade')->onDelete('cascade');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(Option::TABLE)) {
			Schema::table(Option::TABLE, function (Blueprint $table) {
				// Ключи
				$table->dropForeign(Option::TABLE . '_ibfk_1');
				$table->dropForeign(Option::TABLE . '_ibfk_2');
			});
			Schema::drop(Option::TABLE);
		}
	}

}
