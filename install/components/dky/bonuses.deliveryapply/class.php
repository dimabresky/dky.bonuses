<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Page\Asset;

Bitrix\Main\Loader::includeModule('dky.bonuses');

/**
 * Bonuses delivery apply component
 *
 * @author dimabresky
 */
class DkyBonusesDeliveryApply extends CBitrixComponent implements Controllerable {

    /**
     * @return array
     */
    function configureActions(): array {

        $actions = ['cancelDeliveryBonuses', 'applyDeliveryBonuses'];
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

        if ($this->arParams['INCLUDE_JS'] === 'Y' || $this->arParams['INCLUDE_CSS'] === 'Y') {
            $this->InitComponentTemplate();
            $template = $this->GetTemplate();
            $templateFolder = $template->GetFolder();

            if ($this->arParams['INCLUDE_JS'] === 'Y' && file_exists($_SERVER['DOCUMENT_ROOT'] . $templateFolder . "/script.js")) {

                Asset::getInstance()->addString('<script src="' . $templateFolder . '/script.js"></script>');
            }

            if ($this->arParams['INCLUDE_CSS'] === 'Y' && file_exists($_SERVER['DOCUMENT_ROOT'] . $templateFolder . "/style.css")) {

                Asset::getInstance()->addString('<link href="' . $templateFolder . '/style.css" type="text/css" rel="stylesheet">');
            }
            return;
        }

        $this->arResult['DELIVERY_ID'] = intval($this->arParams['DELIVERY_ID']);

        if (!$this->arResult['DELIVERY_ID']) {
            return;
        }

        $deliveryBonusesCost = \dky\Options::getDeliveryBonusesCost($this->arResult['DELIVERY_ID']);

        $arRowBalance = dky\BonusesTable::getCurrentUserBalanceDbRow();

        $storage = new \dky\SessionStorage;

        $arBonusesWithoutDiscountApplyed = $arRowBalance['BONUSES'] - $storage->getApplyedDiscountBonuses();

        if ($deliveryBonusesCost <= 0 || $arBonusesWithoutDiscountApplyed < $deliveryBonusesCost) {
            $storage->clearDelivery();
            return;
        }

        $applyedDeliveryid = $storage->getDeliveryid();

        $this->arResult['APPLYED'] = true;
        if (!$applyedDeliveryid || $this->arResult['DELIVERY_ID'] != $applyedDeliveryid) {
            $this->arResult['APPLYED'] = false;
            $storage->clearDelivery();
        }

        $arBalaceData = \dky\Tools::getCurrentUserBonusesBalance();

        $this->arResult['BALANCE'] = intval($arBalaceData['BONUSES']);

        $this->arResult['DELIVERY_BONUSES_COST'] = $deliveryBonusesCost;
        $this->includeComponentTemplate();
    }

    /**
     * @param int $deliveryid
     * @return boolean
     * @throws Exception
     */
    function applyDeliveryBonusesAction($deliveryid) {

        if (\dky\Tools::canUseBonusesForSite()) {

            if ($deliveryid > 0 && ($deliveryBonusesCost = \dky\Options::getDeliveryBonusesCost($deliveryid)) > 0) {

                $storage = new \dky\SessionStorage;
                $storage->setDeliveryid($deliveryid);
                $storage->setDeliveryBonuses($deliveryBonusesCost);

                return true;
            }

            throw new Exception('Delivery not found.');
        }

        return true;
    }

    /**
     * @return boolean
     */
    function cancelDeliveryBonusesAction() {

        if (\dky\Tools::canUseBonusesForSite()) {
            (new \dky\SessionStorage)->clearDelivery();
        }


        return true;
    }

}
