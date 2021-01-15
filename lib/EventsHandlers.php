<?php

namespace dky;

use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Process events
 * 
 * @author dimabresky
 */
class EventsHandlers {

    /**
     * @global \CUser $USER
     * @param \Bitrix\Sale\Order $order
     * @param bool $isNew
     */
    static function onOrderCreate(Sale\Order $order, $isNew) {

        if ($isNew && Tools::canUseBonusesForSite($order->getSiteId())) {

            $storage = new SessionStorage;
            if ($storage->getApplyedDiscount() > 0) {
                $description = $order->getField('USER_DESCRIPTION');
                $order->setField('USER_DESCRIPTION', $description . "\n\n" . Loc::getMessage('DKY_BONUSES_DISCOUNT_APPLYED', ['#DISCOUNT#' => $storage->getApplyedDiscount()]));

                BonusesHistoryTable::add([
                    'DATE' => DateTime::createFromTimestamp(time()),
                    'USER_ID' => $order->getUserId(),
                    'BONUSES' => (-1) * ceil($storage->getApplyedDiscountBonuses()),
                    'DESCRIPTION' => Loc::getMessage('DKY_BONUSES_HISTORY_DISCOUNT_APPLYED', ['#ORDER_ID#' => $order->getId(), '#DISCOUNT#' => $storage->getApplyedDiscount()])
                ]);
            }
            if ($storage->getApplyedDeliveryBonuses() > 0) {
                $description = $order->getField('USER_DESCRIPTION');
                $order->setField('USER_DESCRIPTION', $description . "\n\n" . Loc::getMessage('DKY_BONUSES_DELIVERY_APPLYED', ['#BONUSES#' => $storage->getApplyedDeliveryBonuses()]));
                BonusesHistoryTable::add([
                    'DATE' => DateTime::createFromTimestamp(time()),
                    'USER_ID' => $order->getUserId(),
                    'BONUSES' => (-1) * ceil($storage->getApplyedDeliveryBonuses()),
                    'DESCRIPTION' => Loc::getMessage('DKY_BONUSES_HISTORY_DELIVERY_APPLYED', ['#ORDER_ID#' => $order->getId(), '#BONUSES#' => $storage->getApplyedDeliveryBonuses()])
                ]);
            }

            $order->save();

            $storage->clear();
        }
    }

    /**
     * @param \Bitrix\Sale\Order $order
     */
    static function onSaleStatusOrderChange($order, $newValue, $oldValue) {

        if (
                $newValue != $oldValue &&
                $newValue == Options::get('STATUS') &&
                Tools::canUseBonusesForSite($order->getSiteId())
        ) {

            $sum = $order->getPrice();

            BonusesHistoryTable::add([
                'DATE' => DateTime::createFromTimestamp(time()),
                'USER_ID' => $order->getUserId(),
                'BONUSES' => ceil($sum * Options::get('BONUSES_COEFF')),
                'DESCRIPTION' => Loc::getMessage('DKY_BONUSES_HISTORY_DESCRIPTION', ['#order_id#' => $order->getId()])
            ]);
        }
    }

    static function addGlobalAdminMenuItem(&$arGlobalMenu, &$arModuleMenu) {
        $arModuleMenu[] = array(
            "parent_menu" => "global_menu_store",
            "section" => "manage_bonuses",
            "sort" => -1,
            "text" => 'Бонусы',
            "title" => 'Бонусы',
            "icon" => '',
            "page_icon" => '',
            "items_id" => "menu_bx_manage_bonuses",
            "url" => "dky_manage_bonuses_list.php?lang=" . LANGUAGE_ID,
            "more_url" => [
                "dky_manage_bonuses.php?lang=" . LANGUAGE_ID
            ]
        );
    }

    static function onSaleComponentOrderOneStepDelivery(&$arResult) {

        global $APPLICATION;
        if (Tools::canUseBonusesForSite()) {

            $APPLICATION->IncludeComponent('dky:bonuses.deliveryapply', '', [
                'INCLUDE_JS' => 'Y',
                'INCLUDE_CSS' => 'Y'
            ]);

            foreach ($arResult['DELIVERY'] as $deliveryid => $arDelivery) {

                if ($arDelivery['CHECKED'] === 'Y') {

                    ob_start();
                    $APPLICATION->IncludeComponent('dky:bonuses.deliveryapply', '', [
                        'DELIVERY_ID' => $deliveryid
                    ]);
                    $arResult["DELIVERY"][$deliveryid]["DESCRIPTION"] .= ob_get_clean();
                    break;
                }
            }
        }

        return true;
    }

    /**
     * @param \Bitrix\Main\EventResult $result
     * @param \Bitrix\Sale\Shipment $shipment
     * @param int $deliveryid
     * @return int
     */
    public static function onSaleDeliveryServiceCalculate($result, $shipment, $deliveryid) {

        if (Tools::canUseBonusesForSite()) {

            $storage = new SessionStorage;
            if ($deliveryid == $storage->getDeliveryid() && \dky\Options::getDeliveryBonusesCost($deliveryid) > 0) {

                $result->setDeliveryPrice(0);
                $shipment->setBasePriceDelivery(0, true);
            }
        }

        return \Bitrix\Main\EventResult::SUCCESS;
    }

}
