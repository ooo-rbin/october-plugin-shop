<?php namespace RBIn\Shop\Traits;

trait CsvController {

	public $importExportConfig = 'csv.yaml';

	public function bootCsvController() {
		$this->implement[] = 'RBIn.Shop.Behaviors.CsvController';
		$this->listConfig['import'] = 'import.yaml';
		$this->listConfig['export'] = 'export.yaml';
	}

	public function makeImportView() {
		$this->vars['csvForm'] = 'importRender';
		$this->vars['csvHandler'] = 'onImportLoadForm';
		return $this->makeCsvView();
	}

	public function makeExportView() {
		$this->vars['csvForm'] = 'exportRender';
		$this->vars['csvHandler'] = 'onExportLoadForm';
		return $this->makeCsvView();
	}

	public function makeCsvView() {
		return $this->makePartial('$/rbin/shop/partials/_csv.htm', $this->vars);
	}

}