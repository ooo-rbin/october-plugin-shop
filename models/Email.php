<?php namespace RBIn\Shop\Models;

use RainLab\User\Models\UserGroup;
use RBIn\Shop\Classes\Model;
use System\Models\MailLayout;
use System\Models\MailTemplate;

class Email extends Model {

	public function getRecipientsOptions() {
		return array_merge([0 => trans('rbin.shop::lang.index.message.recipients.all')], UserGroup::all(['name', 'id'])->lists('name', 'id'));
	}

	public function getLayoutOptions() {
		return MailLayout::all(['name','code'])->lists('name','code');
	}

	public function getTemplateOptions() {
		return MailTemplate::listAllTemplates();
	}

}
