<?php

namespace dky;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Bonuses history table orm class
 *
 * @author dimabresky
 */
class BonusesHistoryTable extends Main\Entity\DataManager {

    static function getTableName() {
        return 'bonuses__history';
    }

    static function getMap() {
        return array(
            new Main\Entity\IntegerField('ID', array(
                'primary' => true
                    )),
            new Main\Entity\IntegerField('BONUSES', [
                'validation' => function () {
                    return [
                        function ($value) {
                            if (empty($value) || !is_numeric($value)) {
                                return Loc::getMessage('DKY_BONUSES_HISTORY_BONUSES_FIELD_ERROR');
                            }

                            return true;
                        }
                    ];
                }
                    ]),
            new Main\Entity\IntegerField('USER_ID', [
                'validation' => function () {
                    return [
                        function ($value) {
                            if ($value <= 0) {
                                return Loc::getMessage('DKY_BONUSES_HISTORY_USER_ID_FIELD_ERROR');
                            }

                            return true;
                        }
                    ];
                }
                    ]),
            new Main\Entity\TextField('DESCRIPTION'),
            new Main\Entity\DateField('DATE', [
                'default_value' => function () {
                    return Main\Type\DateTime::createFromTimestamp(time());
                }
                    ])
        );
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     */
    static function onAfterAdd(Main\Event $event) {

        return self::commonActionAfterSave($event);
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     */
    static function onAfterUpdate(Main\Event $event) {

        return self::commonActionAfterSave($event);
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     */
    static function commonActionAfterSave(Main\Event $event) {
        $result = new Main\Entity\EventResult;

        $fields = $event->getParameter("fields");

        if ($fields['USER_ID']) {

            BonusesTable::recalculationForUser($fields['USER_ID']);
        }

        return $result;
    }

}
