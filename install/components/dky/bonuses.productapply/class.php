<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Sale;
use Bitrix\Currency\CurrencyManager;
use dky\Options as Options;

Bitrix\Main\Loader::includeModule('dky.bonuses');
Bitrix\Main\Loader::includeModule('sale');
Bitrix\Main\Loader::includeModule('catalog');

class DkyBonusesProductApply extends CBitrixComponent implements Controllerable {

    public $basket = null;

    /**
     * @return array
     */
    function configureActions(): array {

        $actions = ['cancelBonusesDiscount', 'applyBonusesDiscount'];
        $options = [];
        foreach ($actions as $action) {
            $options[$action] = [
                new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                ),
                new ActionFilter\Csrf()
            ];
        }
        return $options;
    }

    /**
     * @return $this
     */
    function prepareComponent() {

        $this->basket = Sale\Basket::loadItemsForFUser(
                        Sale\Fuser::getId(),
                        \Bitrix\Main\Context::getCurrent()->getSite()
        );
        return $this;
    }

    /**
     * @global \CUser $USER
     * @return $this
     */
    function setCurrentUserBalance() {

        $this->arResult['BALANCE'] = dky\Tools::getCurrentUserBonusesBalance();
        $this->arResult['MAX_DISCOUNT'] = Options::getMaxDiscountForLevel(intval($this->arResult['BALANCE']['DISCOUNT_LEVEL']));
        if ($this->arResult['BALANCE'] && $this->arResult['BALANCE']['BONUSES'] > 0) {

            $maxCurrentDiscount = $this->arResult['BALANCE']['BONUSES'] * Options::get('BONUSES_DISCOUNT_COEFF');
            if ($maxCurrentDiscount > $this->arResult['MAX_DISCOUNT']) {
                $this->arResult['BALANCE']['DISCOUNT'] = $this->arResult['MAX_DISCOUNT'];
            } else {
                $this->arResult['BALANCE']['DISCOUNT'] = $maxCurrentDiscount;
            }
        }
        return $this;
    }

    function executeComponent() {

        if (!\dky\Tools::canUseBonusesForSite()) {
            return;
        }

        $this->prepareComponent();

        $this->setCurrentUserBalance();

        $this->setApplyedBonusesCoupon();



        if (
                (!$this->arResult['APPLYED_BONUSES_COUPON'] &&
                $this->arResult['BALANCE']['DISCOUNT'] <= 0) ||
                !$this->applyDiscountForDiscount()
        ) {
            return;
        }

        $this->includeComponentTemplate();
    }

    function applyDiscountForDiscount() {

        $discountCalculationResult = $this->getApplyedDiscountList();

        if ($discountCalculationResult &&
                !empty($discountCalculationResult['DISCOUNT_LIST'])) {
            return Options::get('APPLY_DISCOUNT_FOR_DISCOUNT') === 'Y';
        }

        return true;
    }

    /**
     * @return $this
     */
    function setApplyedBonusesCoupon() {

        $storage = new \dky\SessionStorage;

        $this->arResult['APPLYED_BONUSES_COUPON'] = null;
        if (\dky\Tools::bonusesDiscountAreApplyed($storage)) {
            $this->arResult['APPLYED_BONUSES_COUPON'] = [
                'DISCOUNT' => $storage->getApplyedDiscount(),
                'BONUSES' => $storage->getApplyedDiscountBonuses(),
                'COUPON' => $storage->getApplyedCoupon()
            ];
        }

        return $this;
    }

    /**
     * @global \CMain $APPLICATION
     * @global \CUser $USER
     * @global \CDatabase $DB
     * @return boolean
     * @throws Exception
     */
    function applyBonusesDiscountAction() {

        global $APPLICATION, $DB;

        $this->prepareComponent();
        $this->setCurrentUserBalance();
        if ($this->arResult['BALANCE']['DISCOUNT'] <= 0 ||
                !$this->applyDiscountForDiscount()) {
            return false;
        }
        $basketItems = $this->basket->getBasketItems();

        $arConditions = [
            'CLASS_ID' => 'CondGroup',
            'DATA' => [
                'All' => 'AND',
                'True' => 'True'
            ],
            'CHILDREN' => []
        ];
        foreach ($basketItems as $basketItem) {

            $arConditions['CHILDREN'] = [
                'CLASS_ID' => 'CondIBElement',
                'DATA' => [
                    'logic' => 'Equal',
                    'value' => [$basketItem->getProductId()]
                ]
            ];
        }

        $arFields = [
            'SITE_ID' => SITE_ID,
            'ACTIVE' => 'Y',
            'XML_ID' => 'dkybonuses__' . randString(7),
            'RENEWVAL' => 'N',
            'NAME' => md5(serialize($arConditions)),
            'PRIORITY' => 1,
            'SORT' => 100,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'VALUE_TYPE' => 'P',
            'VALUE' => $this->arResult['BALANCE']['DISCOUNT'],
            'CONDITIONS' => $arConditions
        ];

        $DB->StartTransaction();

        $did = CCatalogDiscount::Add($arFields);
        if ($did <= 0) {
            $DB->Rollback();
            if ($ex = $APPLICATION->GetException()) {
                throw new Exception($ex->GetString());
            } else {
                throw new Exception('create discount error');
            }
        }

        $DB->Commit();

        $coupon = CatalogGenerateCoupon();
        $cid = CCatalogDiscountCoupon::Add(
                        array(
                            "DISCOUNT_ID" => $did,
                            "ACTIVE" => "Y",
                            "ONE_TIME" => "O",
                            "COUPON" => $coupon,
                            "DATE_APPLY" => false
        ));

        if (!$cid) {
            throw new Exception('create coupon error');
        }

        // применение бонусов
        Sale\DiscountCouponsManager::add($coupon);

        $discountCalculationResult = $this->getApplyedDiscountList();

        if ($discountCalculationResult && !empty($discountCalculationResult['DISCOUNT_LIST'])) {

            $storage = new \dky\SessionStorage;
            $storage->setCoupon($coupon);
            $storage->setDiscount($this->arResult['BALANCE']['DISCOUNT']);

            return true;
        }

        CCatalogDiscountCoupon::Delete($cid);
        CCatalogDiscount::Delete($did);
        throw new Exception('Apply bonuses discount error');
    }

    /**
     * @global \CDatabase $DB
     * @throws Exception
     */
    function cancelBonusesDiscountAction() {
        gloBal $DB;
        $this->prepareComponent();
        $this->setApplyedBonusesCoupon();
        if ($this->arResult['APPLYED_BONUSES_COUPON']) {

            Sale\DiscountCouponsManager::delete($this->arResult['APPLYED_BONUSES_COUPON']['COUPON']);

            $storage = new \dky\SessionStorage;
            $storage->setDiscount(0);
            $storage->setCoupon('');

            $arFilter = array('COUPON' => $this->arResult['APPLYED_BONUSES_COUPON']['COUPON']);
            $arCoupon = CCatalogDiscountCoupon::GetList(array(), $arFilter, false, false, ['ID', 'DISCOUNT_ID'])->Fetch();
            if ($arCoupon) {
                $DB->StartTransaction();
                if (CCatalogDiscountCoupon::Delete($arCoupon['ID'])) {
                    if (CCatalogDiscount::Delete($arCoupon['DISCOUNT_ID'])) {
                        $DB->Commit();
                        return true;
                    }
                    $DB->Rollback();
                    throw new Exception('Delete discount error');
                }
                $DB->Rollback();
                throw new Exception('Delete coupon error');
            }
        }
        return true;
    }

    function getApplyedDiscountList() {

        // получаем объект скидок для корзины
        $oDiscounts = Sale\Discount::loadByBasket($this->basket);

        // обновляем поля в корзине
        $this->basket->refreshData();

        // пересчёт скидок для корзины
        $oDiscounts->calculate();

        // получаем результаты расчёта скидок для корзины
        $result = $oDiscounts->getApplyResult();

        return $result;
    }

}
