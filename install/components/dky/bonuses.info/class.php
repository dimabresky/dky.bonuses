<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Sale\Delivery\Services\Manager as DelivaryManager;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Services\Base\RestrictionManager;
use Bitrix\Sale\Delivery\Restrictions\BySite;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Config\Option;

Bitrix\Main\Loader::includeModule('dky.bonuses');

/**
 * Bonuses personal info
 *
 * @author dimabresky
 */
class DkyBonusesDeliveryApply extends CBitrixComponent implements Controllerable {

    /**
     * @return array
     */
    function configureActions(): array {

        $actions = ['historyList', 'info'];
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

    function executeComponent() {

        if (!\dky\Tools::canUseBonusesForSite()) {
            return;
        }

        $this->includeComponentTemplate();
    }

    /**
     * @return array
     */
    function infoAction() {

        if (!\dky\Tools::canUseBonusesForSite()) {
            return null;
        }

        $result = $this->getBonusesBalanceData();

        return $this->setDeliveries($result);
    }

    function historyListAction() {

        global $USER;

        if (!\dky\Tools::canUseBonusesForSite()) {
            return [];
        }

        $dbBonusesHistory = dky\BonusesHistoryTable::getList([
                    'filter' => ['USER_ID' => $USER->GetID()],
                    'order' => ['ID' => 'DESC'],
                    'limit' => 100
        ]);
        $list = [];
        while ($arRow = $dbBonusesHistory->fetch()) {

            $list[] = [
                'ID' => $arRow['ID'],
                'DATE' => $arRow['DATE']->toString(),
                'BONUSES' => $arRow['BONUSES'],
                'DESCRIPTION' => $arRow['DESCRIPTION']
            ];
        }

        return $list;
    }

    function charityRequestAction($comment) {

        global $USER;

        if (!\dky\Tools::canUseBonusesForSite()) {
            throw new Exception('Charity request not avail.');
        }

        $arCurrentBalance = $this->getBonusesBalanceData();

        if (!$arCurrentBalance['CHARITY']) {
            throw new Exception('Charity request not avail.');
        }

        $res = dky\BonusesHistoryTable::add([
                    'DATE' => DateTime::createFromTimestamp(time()),
                    'USER_ID' => $USER->GetID(),
                    'BONUSES' => (-1) * $arCurrentBalance['BONUSES'],
                    'DESCRIPTION' => "Charity transfer: {$arCurrentBalance['CHARITY']}BYN"
        ]);

        if ($res->isSuccess()) {

            $text = "Charity transfer: {$arCurrentBalance['CHARITY']}BYN<br>Client: " . ($USER->GetFullName() ?: $USER->GetLogin());
            if ($comment) {
                $text .= "<br>Comment:<br>" . strip_tags(trim($comment));
            }

            $arEmailFields = [
                "EVENT_NAME" => "BONUSES_NOTIFICATIONS",
                "LID" => SITE_ID,
                "C_FIELDS" => array(
                    "SUBJECT" => "",
                    "EMAIL_TO" => dky\Options::get("EMAIL") ?: Option::get('main', 'email_from'),
                    "TEXT" => $text
                ),
            ];
            $messageid = dky\Options::get('EMAIL_MESSAGE_ID');
            if ($messageid > 0) {
                $arEmailFields["MESSAGE_ID"] = $messageid;
            }
            Event::send($arEmailFields);

            return true;
        }

        throw new Exception('Charity transfer is fail.');
    }

    function getBonusesBalanceData() {
        $arCurrentUserBalance = \dky\Tools::getCurrentUserBonusesBalance();

        $result = [
            'BONUSES' => 0,
            'DISCOUNT' => 0,
            'DELIVERIES' => [],
            'CHARITY' => 0
        ];

        $bonusesCharityCoeff = \dky\Options::get('BONUSES_CHARITY_COEFF');

        if ($arCurrentUserBalance) {
            $discount = round($arCurrentUserBalance['BONUSES'] * \dky\Options::get('BONUSES_DISCOUNT_COEFF'), 2);
            $result['BONUSES'] = $arCurrentUserBalance['BONUSES'];
            $result['DISCOUNT'] = $discount > dky\Options::get('MAX_DISCOUNT_PER_PRODUCT') ? dky\Options::get('MAX_DISCOUNT_PER_PRODUCT') : $discount;
            $result['CHARITY'] = $bonusesCharityCoeff ? floor($arCurrentUserBalance['BONUSES'] / $bonusesCharityCoeff) : 0;
        }

        return $result;
    }

    /**
     * @param array $result
     * @return array
     */
    function setDeliveries(array $result) {

        $arDeliveries = DelivaryManager::getActiveList();

        if (!empty($arDeliveries)) {

            $deliveriesid = array_column($arDeliveries, 'ID');

            $arRestrictions = ServiceRestrictionTable::getList([
                        'filter' => [
                            'SERVICE_ID' => $deliveriesid,
                            'SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
                            '=CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\BySite'
                        ],
                        'select' => ['ID', 'SERVICE_ID', 'PARAMS']
                    ])->fetchAll();

            $arDeliveriesidHaveRestrictionBySite = [];
            if (!empty($arRestrictions)) {
                $arDeliveriesidHaveRestrictionBySite = array_combine(array_column($arRestrictions, 'SERVICE_ID'), array_column($arRestrictions, 'PARAMS'));
            }

            foreach ($arDeliveries as $arDelivery) {

                if (isset($arDeliveriesidHaveRestrictionBySite[$arDelivery['ID']])) {

                    if (!BySite::check(SITE_ID, $arDeliveriesidHaveRestrictionBySite[$arDelivery['ID']])) {
                        continue;
                    }
                }

                $deliveryBonusesCost = \dky\Options::getDeliveryBonusesCost($arDelivery['ID']);

                if ($deliveryBonusesCost <= 0) {
                    continue;
                }

                $result['DELIVERIES'][] = [
                    'AVAIL' => \dky\Options::getDeliveryBonusesCost($arDelivery['ID']) <= $result['BONUSES'],
                    'NAME' => $arDelivery['NAME']
                ];
            }
        }



        return $result;
    }

}
