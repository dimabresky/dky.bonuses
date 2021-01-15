<?php

namespace dky;

use Bitrix\Main\UserTable;
use Bitrix\Sale\Order;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

/**
 * Module agents
 *
 * @author dimabresky
 */
class BonusesAgents {

    /**
     * Расчет уровня скидки бонусной системы. Запускается раз в месяц
     * @return string
     */
    static function discountLevelCalculation() {

        $arBonusesTableRows = BonusesTable::getList([
                    'filter' => [
                        'USER_ID' => 1,
                        '!=DISCOUNT_LEVEL' => Options::MAX_DISCOUNT_LEVEL
                    ]
                ])->fetchAll();
        if ($arBonusesTableRows) {

            $arUsersid = array_column($arBonusesTableRows, "USER_ID");
            $arUsersExists = UserTable::getList([
                        'filter' => ['ID' => $arUsersid],
                        'select' => ['ID']
                    ])->fetchAll();
            if ($arUsersExists) {
                $arUsersExists = array_column($arUsersExists, 'ID');
            }
            foreach ($arBonusesTableRows as $arRow) {

                if (in_array($arRow['USER_ID'], $arUsersExists)) {

                    if (!$arRow['DISCOUNT_LEVEL']) {

                        BonusesTable::update($arRow['ID'], ['DISCOUNT_LEVEL' => 1]);
                        continue;
                    }

                    $dbOrders = Order::getList([
                                'filter' => [
                                    'USER_ID' => $arRow['USER_ID'],
                                    '>=DATE_INSERT' => DateTime::createFromTimestamp(mktime(0, 0, 0, date("n") - 1, 1, date("Y")))
                                ],
                                'limit' => 1,
                                'select' => ['ID']
                    ]);
                    if ($dbOrders->fetch()) {
                        BonusesTable::update($arRow['ID'], ['DISCOUNT_LEVEL' => @intval($arRow['DISCOUNT_LEVEL']) + 1]);
                    }

                    if (Options::get('RESET_BONUSES') === 'Y') {
                        $dbBonusesHistory = BonusesHistoryTable::getList([
                                    'filter' => [
                                        '>BONUSES' => 0,
                                        'USER_ID' => $arRow['USER_ID'],
                                        '>=DATE' => DateTime::createFromTimestamp(mktime(0, 0, 0, date("n") - 6, 1, date("Y")))
                                    ],
                                    'limit' => 1,
                                    'select' => ['ID']
                        ]);

                        if (!$dbBonusesHistory->fetch() && $arRow['BONUSES'] > 0) {
                            
                            BonusesHistoryTable::add([
                                'USER_ID' => $arRow['USER_ID'],
                                'DESCRIPTION' => Loc::getMessage('DKY_BONUSES_RESET_BONUSES_SYSTEM'),
                                'BONUSES' => (-1) * $arRow['BONUSES']
                            ]);
                        }
                    }
                }
            }
        }

        return "\dky\Agents::discountLevelCalculation()";
    }

}
