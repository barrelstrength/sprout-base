<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\lists\services;

use barrelstrength\sproutbase\app\lists\base\ListType;
use barrelstrength\sproutbase\app\lists\events\RegisterListTypesEvent;
use barrelstrength\sproutbase\app\lists\records\ListElement as ListsRecord;
use craft\base\Component;
use yii\base\Exception;

/**
 *
 * @property array $allListTypes
 */
class Lists extends Component
{
    /**
     * @event RegisterListTypesEvent
     */
    const EVENT_REGISTER_LIST_TYPES = 'registerSproutListsListTypes';

    /**
     * Registered List Types
     *
     * @var array
     */
    protected $listTypes = [];

    /**
     * Gets all registered list types.
     *
     * @return ListType[]|[]
     */
    public function getAllListTypes(): array
    {
        $event = new RegisterListTypesEvent([
            'listTypes' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_LIST_TYPES, $event);

        $listTypes = $event->listTypes;

        if (!empty($listTypes)) {
            foreach ($listTypes as $listTypeClass) {
                /**
                 * @var ListType $listType
                 */
                $listType = new $listTypeClass();

                $this->listTypes[$listTypeClass] = $listType;
            }
        }

        return $this->listTypes;
    }

    /**
     * Returns a new List Type Class for the given List Type
     *
     * @param $className
     *
     * @return ListType
     * @throws Exception
     */
    public function getListType($className): ListType
    {
        $listTypes = $this->getAllListTypes();

        if (!isset($listTypes[$className])) {
            throw new Exception('Invalid List Type.');
        }

        return new $listTypes[$className];
    }

    /**
     * @param $listId
     *
     * @return ListType
     */
    public function getListTypeById($listId): ListType
    {
        $listRecord = null;

        if (is_numeric($listId)) {
            $listRecord = ListsRecord::findOne($listId);
        } else if (is_string($listId)) {
            $listRecord = ListsRecord::find()->where([
                'handle' => $listId
            ])->one();
        }

        return new $listRecord->type;
    }
}