<?php namespace RBIn\Shop\Traits;

trait CurrentModel {

	public function getCurrentModel() {
		return str_replace(['Import', 'Export'], '', static::class);
	}

}