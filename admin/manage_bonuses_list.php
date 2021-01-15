<?

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");



Loc::loadMessages(__FILE__);

global $USER, $APPLICATION;

if (!$USER->isAdmin()) {
    $APPLICATION->AuthForm("Access denided.");
}

$APPLICATION->SetTitle('Бонусы');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

Bitrix\Main\UI\Extension::load("ui.buttons");
Bitrix\Main\UI\Extension::load("ui.alerts");
Bitrix\Main\Loader::includeModule('dky.bonuses');

$tableid = 'dky_bonuses_list';
$gridOptions = new GridOptions($tableid);
$sort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$navParameters = $gridOptions->GetNavParams();


$nav = new PageNavigation($tableid);
$nav->allowAllRecords(true)
        ->setPageSize($navParameters['nPageSize'])
        ->initFromUri();

// create filter query
$filterOption = new FilterOptions($tableid);
$filterData = $filterOption->getFilter([]);
$filter = [];
if (isset($filterData['FIND']) && strlen($filterData['FIND'])) {
    $find = trim($filterData['FIND']);
    $userFilter = [
        [
            'LOGIC' => 'OR',
            ['NAME' => "%$find%"],
            ['LAST_NAME' => "%$find%"],
            ['SECOND_NAME' => "%$find%"],
            ['EMAIL' => "%$find%"],
            ['LOGIN' => "%$find%"]
        ]
    ];
    $dbUsers = UserTable::getList(['filter' => $userFilter, 'count_total' => true, 'select' => ['ID']]);
    if ($dbUsers->getCount()) {
        while ($arUser = $dbUsers->fetch()) {
            $filter['USER_ID'][] = $arUser['ID'];
        }
    } else {
        $filter['ID'] = -1;
    }
}

//

$dbBonuses = dky\BonusesTable::getList([
            'filter' => $filter,
            'count_total' => true,
            'offset' => $nav->getOffset(),
            'limit' => $nav->getLimit(),
            'order' => $sort['sort']
        ]);
$nav->setRecordCount($dbBonuses->getCount());
$list = [];
while ($arRow = $dbBonuses->fetch()) {
    $arUser = UserTable::getList(['filter' => ['ID' => intval($arRow['USER_ID'])], 'select' => ['ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN']])->fetch();
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

    $list[] = [
        'data' => [
            'ID' => $arRow['ID'],
            'BONUSES' => $arRow['BONUSES'],
            'USER_ID' => $userName
        ],
        'actions' => [
            [
                'text' => 'Подробная информация',
                'default' => true,
                'onclick' => 'document.location.href="/bitrix/admin/dky_manage_bonuses.php?ID=' . $arRow['ID'] . '&lang=' . LANGUAGE_ID . '"'
            ]
        ]
    ];
}
?>

<div class="ui-alert ui-alert-primary">
    <span class="ui-alert-message">
        <strong>
            <?= Loc::getMessage("DKY_BONUSES_LIST_NOTIFY"); ?>
        </strong>
    </span>
</div>

<div>
    <?
    $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        "FILTER_ID" => $tableid,
        "GRID_ID" => $tableid,
        "FILTER" => [],
        "ENABLE_LABEL" => true,
        "ENABLE_LIVE_SEARCH" => true
    ]);
    ?>
    <div style="clear: both"></div>
</div>
<?
$APPLICATION->IncludeComponent('dky:admin.bonuses.popup', '', [
    'POPUP_TITLE' => Loc::getMessage('DKY_BONUSES_LIST_POPUP_TITLE'),
    'POPUP_BTN_TITLE' => Loc::getMessage('DKY_BONUSES_LIST_POPUP_BTN_TITLE')
]);
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $tableid,
    'COLUMNS' => [
        ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
        ['id' => 'BONUSES', 'name' => 'Количество бонусов', 'sort' => 'BONUSES', 'default' => true],
        ['id' => 'USER_ID', 'name' => 'Клиент', 'sort' => 'USER_ID', 'default' => true],
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

