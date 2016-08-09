<?php namespace RBIn\Shop\Models;

use RBIn\Shop\Classes\Model;
use October\Rain\Database\Traits\Sortable;

/**
 * Свойство элемента каталога продукции.
 * @package RBIn\Shop\Models
 */
class OrderedRule extends Model {

	const TABLE = 'rbin_shop_ordered_rules';

	use Sortable;

	const SORT_ORDER = 'order';

	public function getSortOrderAttribute() {
		return $this->{static::SORT_ORDER};
	}

	protected $fillable = [
		'rule_id',
		'title',
		'description',
		'value',
		'order',
	];

	static public function fromRule(Rule $rule) {
		return new static([
			'rule_id' => $rule->{Rule::KEY},
			'title' => $rule->title,
			'description' => $rule->description,
			'value' => $rule->value,
			'order' => $rule->order,
		]);
	}

	public function __construct(array $attributes = []) {
		// Правила
		$this->rules = [
		];
		// Связи
		$this->belongsTo[Order::TABLE] = [
			Order::class,
			'key' => $this->getForeignNames(Order::class)['column'],
			'otherKey' => Order::KEY,
			'order' => Order::CREATED_AT . ' desc',
		];
		$this->belongsTo[Rule::TABLE] = [
			Rule::class,
			'key' => $this->getForeignNames(Rule::class)['column'],
			'otherKey' => Rule::KEY,
			'order' => Rule::SORT_ORDER . ' asc',
		];
		//
		parent::__construct($attributes);
	}

	public function getRuleIdOptions() {
		$rules = Rule::all(['title', Rule::KEY])->lists('title', Rule::KEY);
		if ($this->exists) {
			$rules[$this->rule_id] = $this->title;
		} else {
			$rules[''] = 'rbin.shop::lang.forms.new';
		}
		return $rules;
	}

	public static function fromRuleId($id) {
		$rule = Rule::find($id);
		if (is_null($rule)) {
			return null;
		}
		return new static([
			'rule_id' => $rule->{Rule::KEY},
			'title' => $rule->title,
			'description' => $rule->description,
			'value' => $rule->value,
			static::SORT_ORDER => $rule->{Rule::SORT_ORDER},
		]);
	}

	public function filterFields($fields) {
		$rule = Rule::find($this->rule_id, ['title', 'description', 'value']);
		if (!is_null($rule)) {
			$fields->title->value = $rule->title;
			$fields->description->value = $rule->description;
			$fields->value->value = $rule->value;
		}
	}

}