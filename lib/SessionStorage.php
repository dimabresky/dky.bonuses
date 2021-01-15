<?php

namespace dky;

/**
 * SessionStorage
 *
 * @author dimabresky
 */
class SessionStorage {

    protected $storage = null;

    function __construct() {

        if (!isset($_SESSION['DKY:BONUSES_SYSTEM'])) {
            $_SESSION['DKY:BONUSES_SYSTEM'] = [
                'coupon' => '',
                'discount' => 0,
                'delivery_id' => 0,
                'delivery_bonuses' => 0
            ];
        }


        $this->storage = &$_SESSION['DKY:BONUSES_SYSTEM'];
    }

    /**
     * Save applyed coupon for bonuses system
     * @param string $coupon
     */
    function setCoupon(string $coupon) {
        $this->storage['coupon'] = $coupon;
    }

    /**
     * Return applyed coupon
     * @return string
     */
    function getApplyedCoupon() {
        return $this->storage['coupon'];
    }

    /**
     * Save applyed discount
     * @param float $discount
     */
    function setDiscount(float $discount) {
        $this->storage['discount'] = $discount;
    }

    /**
     * Return applyed discount
     * @return string
     */
    function getApplyedDiscount() {
        return $this->storage['discount'];
    }

    /**
     * Return applyed discount bonuses
     * @return int
     */
    function getApplyedDiscountBonuses() {
        return ceil($this->storage['discount'] / Options::get('BONUSES_DISCOUNT_COEFF'));
    }

    /**
     * Save delivery id
     * @param int $deliveryid
     */
    function setDeliveryid(int $deliveryid) {
        $this->storage['delivery_id'] = $deliveryid;
    }

    /**
     * Return delivery id
     * @return int
     */
    function getDeliveryid() {
        return $this->storage['delivery_id'];
    }

    function setDeliveryBonuses(int $bonuses) {
        $this->storage['delivery_bonuses'] = $bonuses;
    }

    function getApplyedDeliveryBonuses() {
        return $this->storage['delivery_bonuses'];
    }

    function totalSumBonuses() {
        return $this->storage['delivery_bonuses'] + $this->getApplyedDiscountBonuses();
    }

    /**
     * Clear discount data
     */
    function clearDiscount() {
        $this->storage['coupon'] = '';
        $this->storage['discount'] = 0;
    }

    /**
     * Clear delivery bonuses data
     */
    function clearDelivery() {
        $this->storage['delivery_id'] = '';
        $this->storage['delivery_bonuses'] = 0;
    }

    /**
     * Clear storage bonuses data
     */
    function clear() {

        $this->clearDelivery();
        $this->clearDiscount();
    }

}
