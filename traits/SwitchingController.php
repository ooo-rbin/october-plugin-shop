<?php namespace RBIn\Shop\Traits;

use Exception;
use Flash;
use Input;
use Backend\Classes\ListColumn;
use ApplicationException;
use AjaxException;

trait SwitchingController {

	public function switching($recordId = null, $field = 'show') {
		try {
			$model = $this->formFindModelObject($recordId);
			$model->$field = !$model->$field;
			Flash::success(trans('rbin.shop::lang.switching.success'));
			return $this->asExtension('FormController')->makeRedirect('default');
		} catch (Exception $ex) {
			throw new ApplicationException($ex->getMessage());
		}
	}

	public function onSwitching() {
		$ids = Input::get('checked', [Input::get('id')]);
		$class = Input::get('model', false);
		if ($class) {
			$controller = str_plural(str_replace('\\', '', snake_case(class_basename($class))));
			if (!$this->user->hasAccess("rbin.shop.${controller}.*")) {
				throw new ApplicationException(trans('rbin.shop::lang.forms.denied'));
			}
		}
		try {
			foreach ($ids as $id) {
				if ($class) {
					$model = $class::find($id);
				} else {
					$model = $this->formFindModelObject($id);
				}
				$field = Input::get('field', 'show');
				$model->$field = !$model->$field;
				$model->save();
			}
			switch (Input::get('update', 'lamp')) {
				case 'list':
					Flash::success(trans('rbin.shop::lang.switching.successes'));
					return $this->listRefresh();
				default:
					$this->vars = [
						'record' => $model,
						'value' => $model->$field,
						'column' => new ListColumn($field, null)
					];
					return $this->makePartial('$/rbin/shop/partials/_lamp.htm');
			}
		} catch (Exception $ex) {
			throw new ApplicationException($ex->getMessage());
		}
	}

}