<?php namespace RBIn\Shop\Traits;

use Str;
use Model;
use URL;
use RainLab\Blog\Models\Post;
use October\Rain\Router\Helper as RouterHelper;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use RainLab\Pages\Classes\MenuItem;

trait TreeMenuHandler {

	/**
	 * Handler for the pages.menuitem.resolveItem event.
	 * Returns information about a menu item. The result is an array
	 * with the following keys:
	 * - url - the menu item URL. Not required for menu item types that return all available records.
	 *   The URL should be returned relative to the website root and include the subdirectory, if any.
	 *   Use the URL::to() helper to generate the URLs.
	 * - isActive - determines whether the menu item is active. Not required for menu item types that
	 *   return all available records.
	 * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
	 *   The items array should be added only if the $item's $nesting property value is TRUE.
	 * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
	 * @param \Cms\Classes\Theme $theme Specifies the current theme.
	 * @param string $url Specifies the current page URL, normalized, in lower case
	 * The URL is specified relative to the website root, it includes the subdirectory name, if any.
	 * @return mixed Returns an array. Returns null if the item cannot be resolved.
	 */
	public static function resolveMenuItem(MenuItem $item, $url, Theme $theme) {
		$result = null;
		if ($item->type == 'blog-category') {
			if (!$item->reference || !$item->cmsPage) {
				return null;
			}
			$category = self::find($item->reference);
			if (!$category) {
				return null;
			}
			$pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
			if (!$pageUrl) {
				return null;
			}
			$pageUrl = URL::to($pageUrl);
			$result = [];
			$result['url'] = $pageUrl;
			$result['isActive'] = $pageUrl == $url;
			$result['mtime'] = $category->updated_at;
			if ($item->nesting) {
				$categories = $category->getAllRoot();
				$iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
					$branch = [];
					foreach ($categories as $category) {
						$branchItem = [];
						$branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
						$branchItem['isActive'] = $branchItem['url'] == $url;
						$branchItem['title'] = $category->name;
						$branchItem['mtime'] = $category->updated_at;
						if ($category->children) {
							$branchItem['items'] = $iterator($category->children);
						}
						$branch[] = $branchItem;
					}
					return $branch;
				};
				$result['items'] = $iterator($categories);
			}
		} elseif ($item->type == 'all-blog-categories') {
			$result = [
				'items' => []
			];
			$categories = self::orderBy('name')->get();
			foreach ($categories as $category) {
				$categoryItem = [
					'title' => $category->name,
					'url' => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
					'mtime' => $category->updated_at,
				];
				$categoryItem['isActive'] = $categoryItem['url'] == $url;
				$result['items'][] = $categoryItem;
			}
		}
		return $result;
	}

	/**
	 * Returns URL of a category page.
	 */
	protected static function getCategoryPageUrl($pageCode, Model $category, Theme $theme) {
		$page = Page::loadCached($theme, $pageCode);
		if (!$page) return;
		$properties = $page->getComponentProperties('blogPosts');
		if (!isset($properties['categoryFilter'])) {
			return;
		}
		/*
		 * Extract the routing parameter name from the category filter
		 * eg: {{ :someRouteParam }}
		 */
		$filterName = class_basename($category) . 'Filter';
		if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties[$filterName], $matches)) {
			return;
		}
		$paramName = substr(trim($matches[1]), 1);
		$url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);
		return $url;
	}

}