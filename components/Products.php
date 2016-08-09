<?php namespace RBIn\Shop\Components;

use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Relations\HasMany;
use RBIn\Shop\Models\Option;
use Redirect;
use Input;
use Session;
use RBIn\Shop\Classes\Component;
use RBIn\Shop\Models\Product;
use RBIn\Shop\Models\Category;
use RBIn\Shop\Models\Feature;

class Products extends Component {

	public $products;
	public $features;
	public $slug;
	public $page;
	public $category;
	public $session;
	public $pagination;
	public $filters;

	public function componentDetails() {
		return [
			'name' => 'rbin.shop::lang.frontend.products.name',
			'description' => 'rbin.shop::lang.frontend.products.description'
		];
	}

	public function defineProperties() {
		return [
			/*'page' => [
				'title' => 'rbin.shop::lang.frontend.pagination.slug',
				'description' => 'rbin.shop::lang.frontend.pagination.slug_description',
				'type' => 'string',
				'default' => '{{ page }}',
			],*/
			'pagination' => [
				'title' => 'rbin.shop::lang.frontend.pagination.count',
				'description' => 'rbin.shop::lang.frontend.pagination.count_description',
				'type' => 'string',
				'validationPattern' => '^[0-9]+$',
				'validationMessage' => 'rbin.shop::lang.frontend.pagination.count_validation',
				'default' => '10',
			],
			'category' => [
				'title' => 'rbin.shop::lang.frontend.products.category.title',
				'description' => 'rbin.shop::lang.frontend.products.category.description',
				'type' => 'string',
				'default' => '{{ :category }}'
			],
			'session' => [
				'title'       => 'rbin.shop::lang.frontend.products.session.title',
				'description' => 'rbin.shop::lang.frontend.products.session.description',
				'type'        => 'string',
				'default'     => 'products',
			],
		];
	}

	public function onRun()	{
		$this->prepareVars();
		$this->category = $this->loadCategory();
		$this->features = $this->loadFeatures();
		$this->filters = $this->loadFilters();
		$this->products = $this->listProducts();
		/*if ($pageNumberParam = $this->paramName('pageNumber')) {
			$currentPage = $this->property('pageNumber');
			if ($currentPage > ($lastPage = $this->products->lastPage()) && $currentPage > 1) {
				return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
			}
		}*/
		return null;
	}

	public function onProductsFilter() {
		Session::set($this->property('session'), json_encode(Input::get('filters', []), JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		return Redirect::refresh();
	}

	protected function loadFilters() {
		$array = [];
		foreach (json_decode(Session::get($this->session, '[]'), true) as $filter => $restriction) {
			if (isset($this->features[intval($filter)])) {
				$array[intval($filter)] = $restriction;
			} else if ($filter == 'products') {
				$array[$filter] = $restriction;
			} else if ($filter == 'categories') {
				$array[$filter] = $restriction;
			}
		}
		return $array;
	}

	protected function prepareVars() {
		$this->page = intval($this->paramName('page'));
		$this->pagination = intval($this->paramName('pagination'));
		$this->session = $this->property('session');
		$this->slug = $this->property('category');
	}

	protected function listProducts() {
		$products = Product::with(Category::TABLE)->listFrontEnd([
			'page'       => $this->page,
			'pagination' => $this->pagination,
			'category'   => $this->category ? $this->category->id : null,
			'filters'    => $this->filters,
		]);
		return $products;
	}

	protected function loadCategory() {
		if (!$categoryId = $this->property('category'))
			return null;
		if (!$category = Category::where('slug', 'like', $categoryId)->first())
			return null;
		return $category;
	}

	protected function loadFeatures() {
		if (is_null($this->category))
			return null;
		return $this->category->{Feature::TABLE}()->with([Option::TABLE => function (HasMany $query) {
			return $query->groupBy('feature_id', 'value');
		}])->get()->keyBy(Feature::KEY);
	}

	public function sort(Collection $collection) {
		return $collection->sort(function ($a, $b) {
			$av = floatval($a->value);
			$bv = floatval($b->value);
			return ($av == $bv) ? 0 : ($av > $bv) ? 1 : -1;
		});
	}

	public function getAll() {
		return Product::all([Product::KEY, 'title']);
	}

	public function getRoots() {
		return Category::all([Category::KEY, Category::NEST_LEFT, Category::NEST_RIGHT, Category::NEST_DEPTH, Category::PARENT_ID, 'show', 'slug', 'title'])->toNested();
	}

	public function getAllCategories() {
		return Category::where('show', '=', 1)->get(['title', Category::KEY]);
	}

}
