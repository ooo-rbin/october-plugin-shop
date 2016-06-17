<?php namespace RBIn\Shop\Updates;

use October\Rain\Database\Updates\Migration;
use RBIn\Shop\Models\Category;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Models\CategorizedProduct;
use RBIn\Shop\Traits\RelationMigration;

/**
 * Миграция связи продукции и категории каталога.
 * @package RBIn\Shop\Updates
 */
class CreateCategoriesProductsRelation extends Migration {

	use RelationMigration;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		$this->createMoreToMoreRelation(Category::class, Product::class, CategorizedProduct::TABLE);
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		$this->dropMoreToMoreRelation(Category::class, Product::class, CategorizedProduct::TABLE);
	}

}
