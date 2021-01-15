<?php

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class dky_bonuses extends CModule {

    public $MODULE_ID = "dky.bonuses";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "N";
    public $MODULE_DIR = 'bitrix';

    function __construct() {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = "Bonuses system";
        $this->MODULE_DESCRIPTION = "Bonuses system for bitrix shop";
        $this->PARTNER_NAME = "ИП Бреский Дмитрий Игоревич";
        $this->PARTNER_URI = "https://github.com/dimabresky/";

        if (strpos(__DIR__, 'local/modules') !== false) {
            $this->MODULE_DIR = 'local';
        }
    }

    function DoInstall() {

        try {
            ModuleManager::registerModule($this->MODULE_ID);

            // set options
            Option::set($this->MODULE_ID, 'MAX_DISCOUNT', '');
            Option::set($this->MODULE_ID, 'MAX_PRODUCT_COUNT_FOR_DISCOUNT', '');
            Option::set($this->MODULE_ID, 'BONUSES_COEFF', '');
            Option::set($this->MODULE_ID, 'SITE_ID', '');
            Option::set($this->MODULE_ID, 'BONUSES_DISCOUNT_COEFF', '');
            Option::set($this->MODULE_ID, 'BONUSES_CHARITY_COEFF', '');
            Option::set($this->MODULE_ID, 'STATUS', '');
            Option::set($this->MODULE_ID, 'RESET_BONUSES', '');
            Option::set($this->MODULE_ID, 'APPLY_DISCOUNT_FOR_DISCOUNT', '');
            Option::set($this->MODULE_ID, 'EMAIL', '');
            Option::set($this->MODULE_ID, 'MONTH_1', '');
            Option::set($this->MODULE_ID, 'MONTH_2', '');
            Option::set($this->MODULE_ID, 'MONTH_3', '');
            Option::set($this->MODULE_ID, 'MONTH_4', '');

            // install db tables
            $this->installDBTables();

            // install dependencies
            $this->installDependencies();

            // install email event
            $this->installEmailEvent();

            // install bonuses module agents
            $this->installAgents();
        } catch (Exception $ex) {
            $GLOBALS["APPLICATION"]->ThrowException($ex->getMessage());
            $this->DoUninstall();
            return false;
        }

        return true;
    }

    function DoUninstall() {

        // uninstall db tables
        $this->uninstallDBTables();

        // uninstall module dependecies
        $this->uninstallDependencies();

        Option::delete($this->MODULE_ID);

        CAgents::RemoveModuleAgents($this->MODULE_ID);

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    function copyFiles() {
        CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/{$this->MODULE_DIR}/modules/" . $this->MODULE_ID . "/install/admin",
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
                true, true
        );
    }

    function deleteFiles() {
        DeleteDirFiles(
                $_SERVER["DOCUMENT_ROOT"] . "/{$this->MODULE_DIR}/modules/" . $this->MODULE_ID . "/install/admin",
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
    }

    function installDBTables() {
        global $DB;

        // bonuses history table
        if (!$DB->Query('CREATE TABLE IF NOT EXISTS `bonuses__history`('
                        . 'ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                        . 'DATE DATE NOT NULL,'
                        . 'BONUSES INT NOT NULL,'
                        . 'USER_ID INT NOT NULL'
                        . 'DESCRIPTION TEXT'
                        . ')', true)) {
            throw new Exception('Bonuses history table creation error');
        }

        $DB->Query('CREATE INDEX user_id on `bonuses__history`(USER_ID)', true);
        $DB->Query('CREATE INDEX date on `bonuses__history`(DATE)', true);

        // bonuses table
        if (!$DB->Query('CREATE TABLE IF NOT EXISTS `bonuses`('
                        . 'ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,'
                        . 'BONUSES INT NOT NULL,'
                        . 'USER_ID INT NOT NULL'
                        . 'DISCOUNT_LEVEL INT UNSIGNED'
                        . ')', true)) {
            throw new Exception('Bonuses table creation error');
        }

        $DB->Query('CREATE INDEX user_id on `bonuses`(USER_ID)', true);
        $DB->Query('CREATE INDEX bonuses on `bonuses`(BONUSES)', true);
        $DB->Query('CREATE INDEX discount_level on `bonuses`(DISCOUNT_LEVEL)', true);

        return true;
    }

    function uninstallDBTables() {
        global $DB;

        $DB->Query('DROP TABLE IF EXISTS `bonuses`', true);
        $DB->Query('DROP TABLE IF EXISTS `bonuses__history`', true);

        return true;
    }

    function installDependencies() {
        RegisterModuleDependences("sale", "OnSaleOrderSaved", $this->MODULE_ID, "\\dky\\EventsHandlers", "onOrderCreate");
        RegisterModuleDependences("sale", "OnSaleStatusOrderChange", $this->MODULE_ID, "\\dky\\EventsHandlers", "onSaleStatusOrderChange");
        RegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\dky\\EventsHandlers", "addGlobalAdminMenuItem");
        RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", $this->MODULE_ID, "\\dky\\EventsHandlers", "onSaleComponentOrderOneStepDelivery");
        RegisterModuleDependences("sale", "OnSaleDeliveryServiceCalculate", $this->MODULE_ID, "\\dky\\EventsHandlers", "onSaleDeliveryServiceCalculate");
    }

    function uninstallDependencies() {
        UnRegisterModuleDependences("sale", "OnSaleOrderSaved", $this->MODULE_ID, "\\dky\\EventsHandlers", "onOrderCreate");
        UnRegisterModuleDependences("sale", "OnSaleStatusOrderChange", $this->MODULE_ID, "\\dky\\EventsHandlers", "onSaleStatusOrderChange");
        UnRegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\\dky\\EventsHandlers", "addGlobalAdminMenuItem");
        UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", $this->MODULE_ID, "\\dky\\EventsHandlers", "onSaleComponentOrderOneStepDelivery");
        UnRegisterModuleDependences("sale", "OnSaleDeliveryServiceCalculate", $this->MODULE_ID, "\\dky\\EventsHandlers", "onSaleDeliveryServiceCalculate");
    }

    function installEmailEvent() {

        foreach (["ru", "en"] as $lid) {
            $et = new CEventType;
            if (!$et->Add(array(
                        "LID" => $lid,
                        "EVENT_NAME" => "BONUSES_NOTIFICATIONS",
                        "EVENT_TYPE" => "emails",
                        "NAME" => "BUNUSES NOTIFICATIONS",
                        "DESCRIPTION" => ""
                    ))
            ) {
                throw new Exception($et->LAST_ERROR);
            }
        }


        $arFields = array(
            "ACTIVE" => "Y",
            "EVENT_NAME" => "BONUSES_NOTIFICATIONS",
            "LID" => $this->getSiteId(),
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "#EMAIL_TO#",
            "BODY_TYPE" => "html",
            "BCC" => '',
            "CC" => '',
            "REPLY_TO" => '',
            "IN_REPLY_TO" => '',
            "PRIORITY" => '',
            "FIELD1_NAME" => '',
            "FIELD1_VALUE" => '',
            "FIELD2_NAME" => '',
            "FIELD2_VALUE" => '',
            "SITE_TEMPLATE_ID" => '',
            "ADDITIONAL_FIELD" => array(),
            "LANGUAGE_ID" => '',
            "MESSAGE" => "#TEXT#",
            "SUBJECT" => "#SITE_NAME#: #SUBJECT#"
        );

        $emess = new CEventMessage;
        if (!($id = $emess->Add($arFields))) {
            throw new Exception($emess->LAST_ERROR);
        }

        Option::set($this->MODULE_ID, 'EMAIL_MESSAGE_ID', $id);
    }

    public function getSiteId() {

        static $arSites = array();

        if (!empty($arSites)) {
            return $arSites;
        }

        $dbSites = CSite::GetList($by = "sort", $order = "asc");

        while ($arSite = $dbSites->Fetch()) {
            $arSites[] = $arSite['ID'];
        }

        return $arSites;
    }

    function installAgents() {
        $date = ConvertTimeStamp(mktime(0, 0, 0, date('n') + 1, 1, date('Y')));
        CAgent::AddAgent(
                "\dky\BonusesAgents::discountLevelCalculation();",
                $this->MODULE_ID,
                "N",
                86400 * 31,
                $date,
                "Y",
                $date,
                100
        );
    }

}
