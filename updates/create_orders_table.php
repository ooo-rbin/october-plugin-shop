<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Enums\OrderByStatus;
use RBIn\Shop\Enums\OrderByPayment;
use RBIn\Shop\Models\Customer;
use RBIn\Shop\Models\Delivery;
use RBIn\Shop\Models\Payment;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Traits\ForeignNames;

/**
 * Миграция заказов.
 * @package RBIn\Shop\Updates
 */
class CreateOrdersTable extends Migration {

	use ForeignNames;

	public function up() {
		$this->down();
		Schema::create(Order::TABLE, function (Blueprint $table) {
			$customerColumnName = $this->getForeignNames(Customer::class)['column'];
			$deliveryColumnName = $this->getForeignNames(Delivery::class)['column'];
			$paymentColumnName = $this->getForeignNames(Payment::class)['column'];
			$table->engine = 'InnoDB';
			// Колонки
			$table->bigIncrements(Order::KEY, 'primary');
			$table->string('slug');
			$table->enum('status', array_keys(OrderByStatus::getConstants()));
			$table->enum('payment', array_keys(OrderByPayment::getConstants()));
			$table->text('customer_info');
			$table->string('customer_title');
			$table->string('payment_title');
			$table->string('delivery_title');
			$table->unsignedInteger($customerColumnName)->nullable();
			$table->unsignedInteger($paymentColumnName)->nullable();
			$table->unsignedInteger($deliveryColumnName)->nullable();
			$table->decimal('delivery_cost')->nullable();
			$table->boolean('delivery_separately');
			$table->decimal('delivery_payment');
			$table->decimal('payment_cost')->nullable();
			$table->boolean('payment_separately');
			$table->decimal('payment_payment');
			$table->decimal('total_cost')->nullable();
			$table->decimal('total_payment')->nullable();
			$table->nullableTimestamps();
			$table->timestamp('payment_date')->nullable();
			$table->softDeletes();
			// Ключи
			$table->unique('slug', 'unique_order');
			$table->foreign($customerColumnName, Order::TABLE . '_ibfk_1')->references(Customer::KEY)->on(Customer::TABLE)->onUpdate('cascade')->onDelete('set null');
			$table->foreign($deliveryColumnName, Order::TABLE . '_ibfk_2')->references(Delivery::KEY)->on(Delivery::TABLE)->onUpdate('cascade')->onDelete('set null');
			$table->foreign($paymentColumnName, Order::TABLE . '_ibfk_3')->references(Payment::KEY)->on(Payment::TABLE)->onUpdate('cascade')->onDelete('set null');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(Order::TABLE)) {
			Schema::table(Order::TABLE, function (Blueprint $table) {
				// Ключи
				$table->dropForeign(Order::TABLE . '_ibfk_1');
				$table->dropForeign(Order::TABLE . '_ibfk_2');
				$table->dropForeign(Order::TABLE . '_ibfk_3');
			});
			Schema::drop(Order::TABLE);
		}
	}

}
