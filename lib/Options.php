<?php

namespace dky;

use Bitrix\Main\Config\Option;

/**
 * @author dimabresky
 */
class Options {

    /**
     * Масимальный уровень допустимой скидки
     * Скидка на каждый уровень задается в настройках модуля
     */
    const MAX_DISCOUNT_LEVEL = 4;

    /**
     * Количество месяцев для обнуления накопленных бонусов в течении которых
     * клиент не пользуется последними
     */
    const RESET_MONTH_INTERVAL = 6;

    static function get(string $name) {
        return Option::get('dky.bonuses', $name);
    }

    /**
     * Максимально допустимая скидка для уровня
     * @param int $level
     * @return int
     */
    static function getMaxDiscountForLevel(int $level) {

        if ($level >= self::MAX_DISCOUNT_LEVEL) {
            return self::get('MONTH_' . $level);
        }

        return intval(self::get('MONTH_' . $level)) ?: 0;
    }

    /**
     * Return bonuses cost of delivery
     * @param int $deliveryid
     * @return int
     */
    static function getDeliveryBonusesCost(int $deliveryid) {
        return intval(self::get('DELIVERY_' . $deliveryid . '_BONUSES'));
    }

}
