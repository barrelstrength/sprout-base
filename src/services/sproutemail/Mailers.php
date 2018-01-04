<?php

namespace barrelstrength\sproutbase\services\sproutemail;

use barrelstrength\sproutbase\contracts\sproutemail\BaseMailer;
use barrelstrength\sproutbase\events\RegisterMailersEvent;
use craft\base\Component;

class Mailers extends Component
{
	const EVENT_REGISTER_MAILERS = 'defineSproutEmailMailers';

	protected $mailers;

	public function getMailers()
	{
		$event = new RegisterMailersEvent([
			'mailers' => []
		]);

		$this->trigger(self::EVENT_REGISTER_MAILERS, $event);

		$eventMailers = $event->mailers;

		$mailers = [];

		if (!empty($eventMailers))
		{
			foreach ($eventMailers as $eventMailer)
			{
				$namespace = get_class($eventMailer);
				$mailers[$namespace] = $eventMailer;
			}
		}

		return $mailers;
	}

	/**
	 * @param string $name
	 *
	 * @return BaseMailer|null
	 * @internal param bool $includeMailersNotYetLoaded
	 *
	 */
	public function getMailerByName($name)
	{
		$this->mailers = $this->getMailers();

		return isset($this->mailers[$name]) ? $this->mailers[$name] : null;
	}
}