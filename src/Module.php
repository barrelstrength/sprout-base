<?php

namespace barrelstrength\sproutcore;

use yii\base\Event;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\web\View;
use craft\events\RegisterTemplateRootsEvent;


class Module extends \yii\base\Module
{
	public function init()
	{
		parent::init();

		$this->params['foo'] = 'bar';
		// ...  other initialization code ...

		// Register our base template path
		Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
			$e->roots['sprout-core'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
		});

		// Register custom routes
		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
			$event->rules['sprout-settings'] = 'sprout-core/stuff/settings';
			$event->rules['sprout-settings/<pluginName:.*>'] = 'sprout-core/stuff/settings';;
		});
	}

	public function sproutReports()
	{
		return 'cat';
	}
}
