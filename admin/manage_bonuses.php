<?

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION;

if (!$USER->isAdmin()) {
    $APPLICATION->AuthForm("Access denided.");
}

$APPLICATION->SetTitle('История накопления/списания бонусов');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

Bitrix\Main\UI\Extension::load("ui.buttons");
Bitrix\Main\UI\Extension::load("ui.alerts");
Bitrix\Main\Loader::includeModule('dky.bonuses');

$ID = intval($_REQUEST['ID']);

if (!$ID || !($arBonusesRow = \dky\BonusesTable::getRowByID($ID))) {
    ?>
    <div class="ui-alert ui-alert-danger">
        <span class="ui-alert-message">
            <strong>
                <?= Loc::getMessage("DKY_BONUSES_HISTORY_NOT_FOUND"); ?>
            </strong>
        </span>
    </div>
    <?
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
    die;
}

$tableid = 'dky_bonuses_history';
$gridOptions = new GridOptions($tableid);
$sort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$navParameters = $gridOptions->GetNavParams();


$nav = new PageNavigation($tableid);
$nav->allowAllRecords(true)
        ->setPageSize($navParameters['nPageSize'])
        ->initFromUri();

$dbBonusesHistory = dky\BonusesHistoryTable::getList([
            'filter' => ['USER_ID' => $arBonusesRow['USER_ID']],
            'count_total' => true,
            'offset' => $nav->getOffset(),
            'limit' => $nav->getLimit(),
            'order' => $sort['sort']
        ]);
$nav->setRecordCount($dbBonusesHistory->getCount());
$list = [];
while ($arRow = $dbBonusesHistory->fetch()) {

    $list[] = [
        'data' => [
            'ID' => $arRow['ID'],
            'DATE' => $arRow['DATE']->toString(),
            'BONUSES' => $arRow['BONUSES'],
            'DESCRIPTION' => $arRow['DESCRIPTION']
        ]
    ];
}

$arUser = UserTable::getList(['filter' => ['ID' => intval($arBonusesRow['USER_ID'])], 'select' => ['ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN', 'EMAIL']])->fetch();
$userName = '';
if ($arUser) {
    if ($arUser['NAME']) {
        $userName = $arUser['NAME'];
        if ($arUser['SECOND_NAME']) {
            $userName .= ' ' . $arUser['SECOND_NAME'];
        }
        if ($arUser['LAST_NAME']) {
            $userName .= ' ' . $arUser['LAST_NAME'];
        }
    } else {
        $userName = $arUser['LOGIN'];
    }
}
?>

<div class="ui-alert ui-alert-primary">
    <span class="ui-alert-message">

        <?= Loc::getMessage("DKY_BONUSES_HISTORY_USER", ["#USER#" => $userName]); ?>

        <br>
        <?= Loc::getMessage("DKY_BONUSES_HISTORY_BALANCE", ["#BALANCE#" => $arBonusesRow['BONUSES']]); ?><br>

    </span>
</div>

<?
$APPLICATION->IncludeComponent('dky:admin.bonuses.popup', '', [
    'USER_ID' => $arUser['ID'],
    'POPUP_TITLE' => Loc::getMessage('DKY_BONUSES_HISTORY_POPUP_TITLE'),
    'POPUP_BTN_TITLE' => Loc::getMessage('DKY_BONUSES_HISTORY_POPUP_BTN_TITLE')
]);
if ($arUser['EMAIL']) {
    $APPLICATION->IncludeComponent('dky:admin.bonuses.sender', '', [
        'EMAIL' => $arUser['EMAIL']
    ]);
}
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $tableid,
    'COLUMNS' => [
        ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
        ['id' => 'DATE', 'name' => 'Дата зачисления/списания', 'sort' => 'DATE', 'default' => true],
        ['id' => 'BONUSES', 'name' => 'Бонусы', 'sort' => 'BONUSES', 'default' => true],
        ['id' => 'DESCRIPTION', 'name' => 'Примечание', 'default' => true],
    ],
    'ROWS' => $list,
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $nav,
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100'],
        ['NAME' => '1000', 'VALUE' => '1000']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => true,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => true,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => true,
    'AJAX_OPTION_HISTORY' => 'N'
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

