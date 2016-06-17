<?php namespace RBIn\Shop\Updates;

use October\Rain\Database\Updates\Migration;
use RBIn\Shop\Models\Category;
use RBIn\Shop\Models\Feature;
use RBIn\Shop\Models\CategorizedFeatures;
use RBIn\Shop\Traits\RelationMigration;

/**
 * Миграция связи свойств продукции и категории каталога.
 * @package RBIn\Shop\Updates
 */
class CreateCategoriesFeaturesRelation extends Migration {

	use RelationMigration;

	/**
	 * Создание таблицы.
	 */
	public function up() {
		$this->down();
		$this->createMoreToMoreRelation(Category::class, Feature::class, CategorizedFeatures::TABLE);
	}

	/**
	 * Удаление таблицы.
	 */
	public function down() {
		$this->dropMoreToMoreRelation(Category::class, Feature::class, CategorizedFeatures::TABLE);
	}

}
