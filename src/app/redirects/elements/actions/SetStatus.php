<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\app\redirects\elements\actions;

use barrelstrength\sproutbase\app\redirects\elements\Redirect;
use barrelstrength\sproutbase\app\redirects\enums\RedirectMethods;
use barrelstrength\sproutbase\app\redirects\enums\RedirectStatuses;
use barrelstrength\sproutbase\app\redirects\validators\StatusValidator;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\db\Exception;

/**
 *
 * @property string $triggerHtml
 */
class SetStatus extends ElementAction
{
    /**
     * @var string|null The status elements should be set to
     */
    public $status;

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getTriggerHtml(): string
    {
        return Craft::$app->view->renderTemplate('sprout/redirects/_components/elementactions/setstatus');
    }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['status'], 'required'];
        $rules[] = [['status'], StatusValidator::class];

        return $rules;
    }

    /**
     * @param Redirect|ElementQueryInterface $query
     *
     * @return bool
     * @throws Exception
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $status = $this->status;

        // False by default
        $enable = 0;

        switch ($status) {
            case RedirectStatuses::ON:
                $enable = '1';
                break;
            case RedirectStatuses::OFF:
                $enable = '0';
                break;
        }

        $elementIds = $query->ids();

        foreach ($elementIds as $key => $redirectId) {
            /** @var Redirect $redirect */
            $redirect = Craft::$app->getElements()->getElementById($redirectId, Redirect::class, $query->siteId);

            if ((int)$redirect->method === RedirectMethods::PageNotFound) {
                $this->setMessage(Craft::t('sprout', 'Unable to enable a 404. Update redirect method.'));

                return false;
            }
        }

        // Update their statuses
        Craft::$app->db->createCommand()->update(
            '{{%elements}}',
            ['enabled' => $enable],
            ['in', 'id', $elementIds]
        )->execute();

        if ($status == RedirectStatuses::ON) {
            // Enable their locale as well
            Craft::$app->db->createCommand()->update(
                '{{%elements_sites}}',
                ['enabled' => $enable],
                ['and', ['in', 'elementId', $elementIds], '[[siteId]] = :siteId'],
                [':siteId' => $query->siteId]
            )->execute();
        }

        // Clear their template caches
        Craft::$app->templateCaches->deleteCachesByElementId($elementIds);

        $this->setMessage(Craft::t('sprout', 'Statuses updated.'));

        return true;
    }
}
