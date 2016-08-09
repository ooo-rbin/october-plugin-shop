<?php namespace RBIn\Shop\Components;

use RBIn\Shop\Classes\Component;
use RBIn\Shop\Models\Settings;
use RBIn\Shop\Models\Requisite;
use Auth;
use Input;
use System\Models\File;
use Backend\Controllers\Files;

class Requisites extends Component {

	public $user;
	public $requisites;
	public $files;
	public $flash;

	public function componentDetails() {
		return [
			'name' => 'rbin.shop::lang.frontend.requisites.name',
			'description' => 'rbin.shop::lang.frontend.requisites.description'
		];
	}

	public function onRun() {
		$this->prepareVars();
		if (Input::isMethod('post')) {
			$call = explode('/', Input::get('action', ''));
			$args = array_slice($call, 1);
			$call = $call[0];
			if (method_exists($this, $call)) {
				call_user_func_array([$this, $call], $args);
			}
		}
	}

	protected function prepareVars() {
		$this->flash = [];
		foreach (Settings::get('requisites', []) as $requisite) {
			$this->requisites[$requisite['code']] = $requisite;
		}
		$this->user = Auth::getUser();
		if (isset($this->user)) {
			foreach ($this->user->{Requisite::TABLE} as $requisite) {
				if (isset($this->requisites[$requisite->code])) {
					$this->requisites[$requisite['code']]['value'] = $requisite->value;
				} else {
					$requisite->delete();
				}
			}
			$this->files = $this->user->rbin_shop_requisite_files;
		}
	}

	public function saveRequisites() {
		if (isset($this->user)) {
			foreach (Input::get('requisites', []) as $requisite => $value) {
				if (isset($this->requisites[$requisite]) && (empty($this->requisites[$requisite]['rule']) || preg_match('/' . $this->requisites[$requisite]['rule'] . '/', $value))) {
					$r = $this->user->{Requisite::TABLE}()->where('code', 'like', $requisite)->first();
					if (is_null($r)) {
						$r = new Requisite();
						$r->code = $requisite;
						$r->value = $value;
					} else {
						$r->value = $value;
					}
					$this->user->{Requisite::TABLE}()->save($r);
					$this->requisites[$requisite]['value'] = $value;
				}
			}
			foreach (Input::get('files', []) as $file => $title) {
				$this->user->rbin_shop_requisite_files()->where('file_name', 'like', $file)->update([
					'title' => $title,
				]);
			}
			$this->flash[] = [
				'text' => 'Реквизиты обновлены',
				'class' => 'success',
			];
		}
	}

	public function fileAppend() {
		$data = Input::file('file');
		$title = Input::get('title', '');
		if (isset($this->user) && isset($data)) {
			$file = $this->user->rbin_shop_requisite_files()->where('file_name', 'like', $data->getClientOriginalName())->first();
			$sum = $this->user->rbin_shop_requisite_files()->where('file_name', 'not like', $data->getFilename())->sum('file_size') + $data->getClientSize();
			if ($sum > 1024 * 1024 * 4) {
				$this->flash[] = [
					'text' => sprintf('Невозможно загрузить %s. Недостаточно места.', $data->getClientOriginalName()),
					'class' => 'danger',
				];
				return;
			}
			if (isset($file)) {
				$file->delete();
			}
			$file = new File();
			$file->data = $data;
			$file->title = $title;
			$file->is_public = $this->user->rbin_shop_requisite_files()->isPublic();
			$this->user->rbin_shop_requisite_files()->save($file);
			$this->files = $this->user->rbin_shop_requisite_files()->get();
			$this->flash[] = [
				'text' => sprintf('Файл %s загружен', $data->getClientOriginalName()),
				'class' => 'success',
			];
		}
	}

	public function fileRemove($file = '') {
		$this->saveRequisites();
		if (isset($this->user) && !empty($file)) {
			$f = $this->user->rbin_shop_requisite_files()->where('file_name', 'like', $file)->first();
			$f->delete();
			$this->files = $this->user->rbin_shop_requisite_files()->get();
			$this->flash[] = [
				'text' => sprintf('Файл %s удален', $file),
				'class' => 'success',
			];
		}
	}

}