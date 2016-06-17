<?php namespace RBIn\Shop\Updates;

use RBIn\Shop\Models\Product;
use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\Variant;
use RBIn\Shop\Models\OrderedVariant;
use RBIn\Shop\Traits\ForeignNames;

/**
 * Миграция продуктов в заказе.
 * @package RBIn\Shop\Updates
 */
class CreateOrderedVariantsTable extends Migration {

	use ForeignNames;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(OrderedVariant::TABLE, function (Blueprint $table) {
			$orderColumnName = $this->getForeignNames(Order::class)['column'];
			$variantColumnName = $this->getForeignNames(Variant::class)['column'];
			$productColumnName = $this->getForeignNames(Product::class)['column'];
			$table->engine = 'InnoDB';
			// Колонки
			$table->bigIncrements(OrderedVariant::KEY, 'primary');
			$table->string('title');
			$table->unsignedBigInteger($orderColumnName);
			$table->unsignedInteger($variantColumnName)->nullable();
			$table->unsignedInteger($productColumnName)->nullable();
			$table->unsignedInteger('amount');
			$table->decimal('cost');
			$table->integer('order')->nullable();
			// Ключи
			$table->foreign($orderColumnName, OrderedVariant::TABLE . '_ibfk_1')->references(Order::KEY)->on(Order::TABLE)->onUpdate('cascade')->onDelete('cascade');
			$table->foreign($variantColumnName, OrderedVariant::TABLE . '_ibfk_2')->references(Variant::KEY)->on(Variant::TABLE)->onUpdate('cascade')->onDelete('set null');
			$table->foreign($productColumnName, OrderedVariant::TABLE . '_ibfk_3')->references(Product::KEY)->on(Product::TABLE)->onUpdate('cascade')->onDelete('set null');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(OrderedVariant::TABLE)) {
			Schema::table(OrderedVariant::TABLE, function (Blueprint $table) {
				// Ключи
				$table->dropForeign(OrderedVariant::TABLE . '_ibfk_1');
				$table->dropForeign(OrderedVariant::TABLE . '_ibfk_2');
				$table->dropForeign(OrderedVariant::TABLE . '_ibfk_3');
			});
			Schema::drop(OrderedVariant::TABLE);
		}
	}

}
