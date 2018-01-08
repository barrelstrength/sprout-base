<?php

namespace barrelstrength\sproutbase\models\sproutemail;

use craft\base\Component;

class EmailMessage extends Component
{
	public $model;
	public $body;
	public $htmlBody;
	public $subject;
	public $recipients;
	public $fromName;
	public $fromEmail;
	public $replyToEmail;
	public $enableFileAttachments;
}