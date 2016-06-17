<?php namespace RBIn\Shop\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use RBIn\Shop\Models\Category;

/**
 * Миграция категорий продуктов.
 * @package RBIn\Shop\Updates
 */
class CreateCategoriesTable extends Migration {

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		Schema::create(Category::TABLE, function (Blueprint $table) {
			$table->engine = 'InnoDB';
			// Колонки
			$table->increments(Category::KEY, 'primary');
			$table->string('slug');
			$table->boolean('show');
			$table->string('title');
			$table->text('annotation');
			$table->text('description');
			$table->string('keywords');
			$table->string('meta_title');
			$table->string('meta_description');
			$table->unsignedInteger(Category::PARENT_ID)->nullable();
			$table->integer(Category::NEST_DEPTH)->nullable();
			$table->integer(Category::NEST_LEFT)->nullable();
			$table->integer(Category::NEST_RIGHT)->nullable();
			// Ключи
			$table->unique('slug', 'unique_category');
			$table->foreign(Category::PARENT_ID, Category::TABLE . '_ibfk_1')->references(Category::KEY)->on(Category::TABLE)->onUpdate('cascade')->onDelete('set null');
		});
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		if (Schema::hasTable(Category::TABLE)) {
			Schema::table(Category::TABLE, function (Blueprint $table) {
				$table->dropForeign(Category::TABLE . '_ibfk_1');
			});
			Schema::drop(Category::TABLE);
		}

	}

}
