<?php namespace RBIn\Shop;

use RainLab\User\Models\User;

if (!defined(__NAMESPACE__ . '\\UserTable') || !defined(__NAMESPACE__ . '\\UserKey')) {
	$user = new User();
	if (!defined(__NAMESPACE__ . '\\UserTable')) {
		define(__NAMESPACE__ . '\\UserTable', $user->getTable());
	}
	if (!defined(__NAMESPACE__ . '\\UserKey')) {
		define(__NAMESPACE__ . '\\UserKey', $user->getKeyName());
	}
	unset($user);
}