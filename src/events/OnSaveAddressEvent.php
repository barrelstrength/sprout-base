<?php
namespace barrelstrength\sproutcore\events;

use yii\base\Event;

class OnSaveAddressEvent extends Event
{
	// Properties
	// =========================================================================

	public $model  = null;
	public $source = null;
}
