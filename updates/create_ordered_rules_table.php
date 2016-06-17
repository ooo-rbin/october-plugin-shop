<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Order;
use RBIn\Shop\Models\Rule;
use RBIn\Shop\Models\OrderedRule;
use RBIn\Shop\Traits\ForeignNames;

/**
 * Миграция правил для заказа.
 * @package RBIn\Shop\Updates
 */
class CreateOrderedRulesTable extends Migration {

	use ForeignNames;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(OrderedRule::TABLE, function (Blueprint $table) {
			$orderColumnName = $this->getForeignNames(Order::class)['column'];
			$ruleColumnName = $this->getForeignNames(Rule::class)['column'];
			$table->engine = 'InnoDB';
			// Колонки
			$table->bigIncrements(OrderedRule::KEY, 'primary');
			$table->unsignedBigInteger($orderColumnName);
			$table->unsignedInteger($ruleColumnName)->nullable();
			$table->string('title');
			$table->text('value');
			$table->integer('order')->nullable();
			// Ключи
			$table->unique('title', 'unique_ordered_rule');
			$table->foreign($orderColumnName, OrderedRule::TABLE . '_ibfk_1')->references(Order::KEY)->on(Order::TABLE)->onUpdate('cascade')->onDelete('cascade');
			$table->foreign($ruleColumnName, OrderedRule::TABLE . '_ibfk_2')->references(Rule::KEY)->on(Rule::TABLE)->onUpdate('cascade')->onDelete('set null');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(OrderedRule::TABLE)) {
			Schema::table(OrderedRule::TABLE, function (Blueprint $table) {
				// Ключи
				$table->dropForeign(OrderedRule::TABLE . '_ibfk_1');
				$table->dropForeign(OrderedRule::TABLE . '_ibfk_2');
			});
			Schema::drop(OrderedRule::TABLE);
		}
	}

}
