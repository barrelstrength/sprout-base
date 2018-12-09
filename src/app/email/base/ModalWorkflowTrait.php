<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\ModalResponse;
use barrelstrength\sproutbase\SproutBase;
use Craft;

trait ModalWorkflowTrait
{
    /**
     * Gives mailers the ability to include their own modal resources for the Email Element Index page
     *
     * @example
     * Mailers should be calling the following functions from within their implementation
     *
     * Craft::$app->getView()->registerAssetBundle(MyMailerAsset::class);
     */
    public function includeModalResources()
    {
        return null;
    }

    /**
     * Gives a mailer the ability to register an action to post to when a [prepare] modal is launched.
     *
     * @example
     *
     * The Copy/Paste mailer uses this to handle the Copy/Paste workflow instead of a Send workflow
     *
     * @return string
     */
    public function getActionForPrepareModal(): string
    {
        return 'sprout/mailers/get-prepare-modal';
    }

    /**
     * @param EmailElement $email
     *
     * @return ModalResponse
     * @throws \Throwable
     */
    public function getPrepareModal(EmailElement $email): ModalResponse
    {
        $response = new ModalResponse();

        try {
            $response->success = true;
            $response->content = $this->getPrepareModalHtml($email);

            return $response;
        } catch (\Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();

            return $response;
        }
    }

    /**
     * @param EmailElement $email
     *
     * @return string
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getPrepareModalHtml(EmailElement $email): string
    {
        // Display the testToEmailAddress if it exists
        $recipients = Craft::$app->getConfig()->getGeneral()->testToEmailAddress;

        if (empty($recipients)) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $recipients = $currentUser->email;
        }

        $errors = [];

        // This processes the whole email to check for errors ahead of time
        // @todo - review - this should just be $email->getErrors()? $mailer->getErrors()? $message->getErrors()?
        $errors = SproutBase::$app->notifications->getNotificationErrors($email, $errors);

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-email/_modals/prepare-email-snapshot',
            [
                'email' => $email,
                'recipients' => $recipients,
                'errors' => $errors
            ]
        );
    }
}
