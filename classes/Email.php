<?php namespace RBIn\Shop\Classes;

use Illuminate\Database\Query\Builder;
use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;
use Input;
use Redirect;
use Flash;
use Mail;
use Illuminate\Mail\Message;
use Config;
use System\Models\MailLayout;
use System\Models\MailTemplate;

class Email {

	public $exists = false;

	public function sendMail() {
		$group = intval(Input::get('recipients', 0));
		if ($group == 0) {
			$to = User::all(['email'])->lists('email');
		} else {
			$to = User::whereIn('id', function (Builder $query) use ($group) {
				$query->select('user_id')->from('users_groups')->where('user_group_id', $group);
			})->get(['email'])->lists('email');
		}
		if (empty($to)) {
			Flash::success(trans('rbin.shop::lang.index.message.nothing'));
		} else {
			Mail::sendTo($to, 'rbin.shop::mail.message', [
				'text' => Input::get('message', ''),
			], function (Message $message) {
				$path = Config::get('filesystems.disks.local.root', storage_path()) . DIRECTORY_SEPARATOR . Config::get('cms.storage.media.folder', 'media');
				$message->subject(Input::get('subject', ''));
				foreach (Input::get('attachments', []) as $attachment) {
					$message->attach($path . DIRECTORY_SEPARATOR . $attachment['type']);
				}
			});
			Flash::success(trans('rbin.shop::lang.index.message.complete'));
		}
		return Redirect::refresh();
	}

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