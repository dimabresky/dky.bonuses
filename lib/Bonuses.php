<?php

namespace dky;

use Bitrix\Main\Entity;

/**
 * Bonuses table orm class
 *
 * @author dimabresky
 */
class BonusesTable extends Entity\DataManager {

    static function getTableName() {
        return 'bonuses';
    }

    static function getMap() {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true
                    )),
            new Entity\IntegerField('BONUSES'),
            new Entity\IntegerField('USER_ID'),
            new Entity\IntegerField('DISCOUNT_LEVEL')
        );
    }

    /**
     * @param int $userid
     * @param int $bonuses
     */
    static function recalculationForUser(int $userid) {
        $dbBonusesHistory = BonusesHistoryTable::getList([
                    'filter' => ['=USER_ID' => $userid],
                    'select' => ['BONUSES']
        ]);

        $bonuses = 0;
        while ($arRow = $dbBonusesHistory->fetch()) {
            $bonuses += $arRow['BONUSES'];
        }

        $arBonusesRow = self::getList([
                    'filter' => [
                        '=USER_ID' => $userid
                    ],
                    'select' => ['ID']
                ])->fetch();

        if ($arBonusesRow) {
            self::update($arBonusesRow['ID'], ['BONUSES' => $bonuses]);
        } else {
            self::add([
                'BONUSES' => $bonuses,
                'USER_ID' => $userid
            ]);
        }

        return $bonuses;
    }

    /**
     * Return row balance like array for current user
     * @global \CUser $USER
     * @return array|null
     */
    static function getCurrentUserBalanceDbRow() {

        global $USER;

        return self::getUserBalanceDbRow(intval($USER->GetID()));
    }

    /**
     * Return row balance like array by user id
     * @param int $userid
     * @return array|null
     */
    static function getUserBalanceDbRow(int $userid) {
        return self::getList([
                    'filter' => ['USER_ID' => $userid]
                ])->fetch();
    }

}
