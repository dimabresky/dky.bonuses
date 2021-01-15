<?php
if (!$USER->isAdmin())
    return;
$mid = "dky.bonuses";
Bitrix\Main\Loader::includeModule('sale');
Bitrix\Main\Loader::includeModule($mid);



global $APPLICATION;

function renderOptions($arOptions, $mid) {

    foreach ($arOptions as $name => $arValues) {

        $cur_opt_val = htmlspecialcharsbx(Bitrix\Main\Config\Option::get($mid, $name));
        $name = htmlspecialcharsbx($name);

        $options .= '<tr>';
        $options .= '<td width="40%">';
        $options .= '<label for="' . $name . '">' . $arValues['DESC'] . ':</label>';
        $options .= '</td>';
        $options .= '<td width="60%">';
        if ($arValues['TYPE'] == 'multiple_checkboxes') {

            $arCurValues = [];

            if ($cur_opt_val) {
                $arCurValues = explode("|", $cur_opt_val);
            }

            foreach ($arValues['VALUES'] as $arVal) {
                $options .= '<input type="hidden" name="' . $name . '[]" value="N">';
                $options .= '<input type="checkbox" ' . (in_array($arVal['VALUE'], $arCurValues) ? "checked" : "") . ' name="' . $name . '[]" value="' . $arVal['VALUE'] . '"> [' . $arVal['VALUE'] . ']' . $arVal['TITLE'] . '<br>';
            }
        } elseif ($arValues['TYPE'] == 'select') {

            $options .= '<select id="' . $name . '" name="' . $name . '">';
            foreach ($arValues['VALUES'] as $key => $value) {
                $options .= '<option ' . ($cur_opt_val == $key ? 'selected' : '') . ' value="' . $key . '">' . $value . '</option>';
            }
            $options .= '</select>';
        } elseif ($arValues['TYPE'] == 'text') {

            $options .= '<input type="text" name="' . $name . '" value="' . $cur_opt_val . '">';
        } elseif ($arValues['TYPE'] == 'checkbox') {

            $options .= '<input type="hidden" name="' . $name . '" value="N">';
            $options .= '<input type="checkbox" ' . ($cur_opt_val === "Y" ? "checked" : "") . ' name="' . $name . '" value="Y">';
        } elseif ($arValues['TYPE'] == 'file') {

            $options .= '<input type="file" name="' . $name . '">';
        } elseif ($arValues['TYPE'] === 'bonuses_coeff') {
            $options .= '1 рубль=<input size="1" type="text" name="' . $name . '" value="' . $cur_opt_val . '"> бонусов';
        } elseif ($arValues['TYPE'] === 'bonuses_discount_coeff') {
            $options .= '1 бонус=<input size="1" type="text" name="' . $name . '" value="' . $cur_opt_val . '">% скидки';
        } elseif ($arValues['TYPE'] === 'bonuses_charity_coeff') {
            $options .= '1 рубль=<input size="1" type="text" name="' . $name . '" value="' . $cur_opt_val . '"> бонусов';
        }
        $options .= '</td>';
        $options .= '</tr>';
    }
    echo $options;
}

$dbStatuses = \Bitrix\Sale\Internals\StatusLangTable::getList(array(
            'order' => array('STATUS.SORT' => 'ASC'),
            'filter' => array('STATUS.TYPE' => 'O', 'LID' => 'ru'),
            'select' => array('STATUS_ID', 'NAME', 'DESCRIPTION'),
        ));

$arStatuses = [];
while ($arStatus = $dbStatuses->fetch()) {
    $arStatuses[$arStatus['STATUS_ID']] = $arStatus['NAME'];
}


$rsSites = CSite::GetList($by = "ID", $order = "ASC");
$arSites = [];
while ($arSite = $rsSites->Fetch()) {
    $arSites[] = ['VALUE' => $arSite['LID'], 'TITLE' => $arSite['NAME']];
}

$main_options = array(
    'TABS' => array(
        "TOTAL" => [
            "TITLE" => "",
            "OPTIONS" => [
                "SITE_ID" => array("DESC" => "Сайты, c заказов которых происходит начисление бонусов", 'TYPE' => 'multiple_checkboxes', 'VALUES' => $arSites),
                "BONUSES_COEFF" => array("DESC" => "Коэффициент зачисления бонусов", 'TYPE' => 'bonuses_coeff'),
                "BONUSES_DISCOUNT_COEFF" => array("DESC" => "Коэффициент перевода бонусов в скидку", 'TYPE' => 'bonuses_discount_coeff'),
                "BONUSES_CHARITY_COEFF" => array("DESC" => "Коэффициент перевода бонусов в благотворительность", 'TYPE' => 'bonuses_charity_coeff'),
                "STATUS" => array("DESC" => "Статус заказа при котором зачислять бонусы клиенту", 'TYPE' => 'select', 'VALUES' => $arStatuses),
                "EMAIL" => array("DESC" => "Email для приема уведомлений<br><small>Если не указан, то будет взят из настроек главного модуля", 'TYPE' => 'text'),
                "RESET_BONUSES" => array("DESC" => "Включить обнуление бонусов, если клиент не пользовался бонусами в течении 6 месяцев", 'TYPE' => 'checkbox'),
                "APPLY_DISCOUNT_FOR_DISCOUNT" => array("DESC" => "Применять бонусную скидку к товарам или корзине к которым уже применены скидки", 'TYPE' => 'checkbox')
            ]
        ],
        "DISCOUNT" => [
            "TITLE" => "Размер скидки",
            "DESCRIPTION" => "Первый месяц использования бонусов, а также помесячное увеличение допустимого размера скидки"
            . " начинается с первого числа месяца, который следует за текущим,"
            . " если клиентом была совершена хотя бы одна покупка в текущем месяце.",
            "OPTIONS" => []
        ],
    )
);

for ($i = 1; $i <= \dky\Options::MAX_DISCOUNT_LEVEL; $i++) {
    if ($i !== \dky\Options::MAX_DISCOUNT_LEVEL) {
        $main_options['TABS']['DISCOUNT']['OPTIONS']['MONTH_' . $i] = ["DESC" => "Максимальная скидка(%) в {$i}-й месяц использования бонусов", 'TYPE' => 'text'];
    } else {
        $main_options['TABS']['DISCOUNT']['OPTIONS']['MONTH_' . $i] = ["DESC" => "Максимальная скидка(%) в {$i}-й и последующие месяцы использования бонусов", 'TYPE' => 'text'];
    }
}

$dbDeliveries = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();

if (!empty($dbDeliveries)) {
    $main_options['TABS']['DELIVERY'] = [
        "TITLE" => "Стоимость доставки",
        "OPTIONS" => []
    ];
    foreach ($dbDeliveries as $arr) {
        $main_options['TABS']['DELIVERY']['OPTIONS']['DELIVERY_' . $arr['ID'] . '_BONUSES'] = array("DESC" => "Стоимость в бонусах доставки \"{$arr['NAME']}\" <br><small>если 0 или пустое значение, то доставка не учавствует в бонусоной системе</small>", 'TYPE' => 'text');
    }
}

$tabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => "Настройки модуля",
        "ICON" => "",
        "TITLE" => "Настройки модуля"
    ),
);

$o_tab = new CAdminTabControl("TravelsoftTabControl", $tabs);
if ($REQUEST_METHOD == "POST" && strlen($save . $reset) > 0 && check_bitrix_sessid()) {

    if (strlen($reset) > 0) {
        foreach ($main_options as $arTabs) {
            foreach ($arTabs as $arBlockOption) {
                foreach (array_keys($arBlockOption['OPTIONS']) as $name) {
                    \Bitrix\Main\Config\Option::set($mid, $name, '');
                }
            }
        }
    } else {
        foreach ($main_options as $arTabs) {
            foreach ($arTabs as $arBlockOption) {
                foreach (array_keys($arBlockOption['OPTIONS']) as $name) {

                    if (is_array($_REQUEST[$name])) {
                        $values = array_filter($_REQUEST[$name], function ($val) {
                            return $val !== 'N';
                        });

                        \Bitrix\Main\Config\Option::set($mid, $name, !empty($values) ? implode("|", $values) : '');
                    } else {
                        \Bitrix\Main\Config\Option::set($mid, $name, trim($_REQUEST[$name]));
                    }
                }
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&" . $o_tab->ActiveTabParam());
}
$o_tab->Begin();
?>

<form enctype="multipart/form-data" method="post" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<? echo LANGUAGE_ID ?>">
    <?
    foreach ($main_options as $arTabs) {
        $o_tab->BeginNextTab();
        foreach ($arTabs as $arTab) {

            if ($arTab['TITLE']) {
                ?>
                <tr class="heading">
                    <td colspan="2"><?= $arTab['TITLE'] ?></td>
                </tr><?
            }
            if ($arTab['DESCRIPTION']) {
                ?>
                <tr>
                    <td align="center" colspan="2">
                        <?= BeginNote() ?>
                        <?= $arTab['DESCRIPTION'] ?>
                        <?= EndNote() ?>
                    </td>

                </tr><?
            }
            renderOptions($arTab['OPTIONS'], $mid);
        }
    }
    $o_tab->Buttons();
    ?>
    <input type="submit" name="save" value="Сохранить" title="Сохранить" class="adm-btn-save">
    <input type="submit" name="reset" title="Сбросить" OnClick="return confirm('Уверены ?')" value="Сбросить">
    <?= bitrix_sessid_post(); ?>
    <? $o_tab->End(); ?>
</form>
