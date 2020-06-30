<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\integrations\sproutreports\datasources;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\lists\listtypes\SubscriberList;
use barrelstrength\sproutbase\app\lists\records\ListElement as ListElementRecord;
use barrelstrength\sproutbase\app\lists\records\Subscriber as SubscriberRecord;
use Craft;
use craft\db\Query;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class SubscriberListDataSource
 *
 * @package barrelstrength\sproutforms\integrations\sproutreports\datasources
 *
 * @property string $defaultEmailColumn
 */
class SubscriberListDataSource extends DataSource
{
    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Subscriber List (Sprout Lists)');
    }

    /**
     * @return null|string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout', 'Create a Subscriber List with your Subscribers');
    }

    /**
     * @return bool
     */
    public function isEmailColumnEditable(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getDefaultEmailColumn(): string
    {
        return 'email';
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getResults(Report $report, array $settings = []): array
    {
        $reportSettings = $report->getSettings();

        /** @var ListElementRecord $listRecord */
        $listRecord = ListElementRecord::find()
            ->where([
                'id' => $reportSettings['subscriberListId']
            ])
            ->one();

        /** @var SubscriberRecord $subscriberRecords */
        $subscriberRecords = $listRecord->getSubscribers()->all();

        $subscribers = [];
        foreach ($subscriberRecords as $subscriberRecord) {
            $subscribers[] = $subscriberRecord->getAttributes();
        }

        return $subscribers;
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        $subscriberListOptions = (new Query())
            ->select([
                'lists.name AS label',
                'lists.id AS value'
            ])
            ->from(ListElementRecord::tableName().' lists')
            ->leftJoin('{{%elements}} elements', '[[elements.id]] = [[lists.id]]')
            ->where([
                'lists.type' => SubscriberList::class,
                'elements.dateDeleted' => null
            ])
            ->all();

        return Craft::$app->getView()->renderTemplate('sprout/lists/_integrations/sproutreports/datasources/SubscriberList/settings', [
            'subscriberListOptions' => $subscriberListOptions
        ]);
    }

//    /**
//     * @inheritdoc
//     *
//     * @throws Exception
//     */
//    public function prepSettings(array $settings)
//    {
//        // Convert date strings to DateTime
//        $settings['startDate'] = DateTimeHelper::toDateTime($settings['startDate']) ?: null;
//        $settings['endDate'] = DateTimeHelper::toDateTime($settings['endDate']) ?: null;
//
//        return $settings;
//    }
}
