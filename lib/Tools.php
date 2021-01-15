<?php

namespace dky;

use Bitrix\Sale;

/**
 * Tools
 *
 * @author dimabresky
 */
class Tools {

    /**
     * @param string $siteid
     * @return bool
     */
    static function canUseBonusesForSite(string $siteid = null) {

        global $USER;

        if (!$siteid && defined('SITE_ID')) {
            $siteid = SITE_ID;
        }

        $arSiteid = explode("|", Options::get('SITE_ID'));
        return in_array($siteid, $arSiteid) && $USER->IsAuthorized();
    }

    /**
     * Return current user bonuses balance with applyed bonuses
     * @return array|null
     */
    static function getCurrentUserBonusesBalance() {
        $arr = BonusesTable::getCurrentUserBalanceDbRow();

        $storage = new SessionStorage();
        if ($arr && (self::bonusesDiscountAreApplyed($storage) || $storage->getApplyedDeliveryBonuses() > 0)) {

            $arr['BONUSES'] -= $storage->getApplyedDiscountBonuses();
            $arr['BONUSES'] -= $storage->getApplyedDeliveryBonuses();
        }

        return $arr;
    }

    /**
     * Return bonuses discount are applyed
     * @param \dky\SessionStorage $storage
     * @return bool
     */
    static function bonusesDiscountAreApplyed(SessionStorage $storage) {

        \Bitrix\Main\Loader::includeModule('sale');

        $arApplyedCoupons = Sale\DiscountCouponsManager::get(false, [], false, true);

        return !empty($arApplyedCoupons) &&
                !empty($coupon = $storage->getApplyedCoupon()) &&
                in_array($coupon, $arApplyedCoupons);
    }

}
