<?php
Bitrix\Main\UI\Extension::load("ui.buttons");
Bitrix\Main\UI\Extension::load("ui.forms");

Bitrix\Main\UI\Extension::load("ui.alerts");
CJSCore::Init(['ajax', 'popup']);
?>
<template id="bonuses-popup-content">

    <div class="bonuses-popup__form bonuses-form">
        <h2><?= $arParams['POPUP_TITLE'] ?></h2>
        <div class="ui-alert ui-alert-primary">
            <span class="ui-alert-message">
                <strong>
                    <?= GetMessage("DKY_BONUSES_FORM_NOTIFY"); ?>
                </strong>
            </span>
        </div>
        <form action="<?= $APPLICATION->GetCurPageParam() ?>" method="post">
            <? if (!empty($arResult['ERRORS'])): ?>
                <div class="ui-alert ui-alert-danger">
                    <span class="ui-alert-message">
                        <strong>
                            <?
                            foreach ($arResult['ERRORS'] as $error) {
                                echo $error . '<br>';
                            }
                            ?>
                        </strong>
                    </span>
                </div>
            <? endif ?>
            <?= bitrix_sessid_post()?>
            <? if ($arParams['USER_ID']): ?>
                <input name="bonuses_row[USER_ID]" value="<?= $arParams['USER_ID'] ?>" type="hidden">
            <? endif ?>
            <div class="bonuses-form__form-group">

                <div class="ui-ctl ui-ctl-after-icon">
                    <input name="bonuses_row[BONUSES]" placeholder="<?= GetMessage("DKY_BONUSES_POPUP_BONUSES_FIELD_TITLE") ?>*" value="<?= $arResult['FORM_DATA']['BONUSES'] ?>" type="text" class="ui-ctl-element ui-ctl-textbox">
                </div>
            </div>
            <? if (!$arParams['USER_ID']): ?>
                <div class="bonuses-form__form-group">

                    <div class="relative-block ui-ctl ui-ctl-after-icon">
                        <div class="ui-ctl-after ui-ctl-icon-search"></div>
                        <input autocomplete="off"  id="find-users-input" oninput="DKYBonuses.findUsers(event);" placeholder="<?= GetMessage("DKY_BONUSES_POPUP_CLIENT_FIELD_TITLE") ?>*" value="<?= $arResult['FORM_DATA']['USER']['NAME']?>" type="text" class="ui-ctl-element ui-ctl-textbox">
                        <div id="find-users-block" class="find-users-block hide">
                            
                        </div>
                        <input id="user-id-input" name="bonuses_row[USER_ID]" value="<?= $arResult['FORM_DATA']['USER']['ID']?>" type="hidden">
                    </div>
                </div>
            <? endif ?>
            <div class="bonuses-form__form-group">

                <div class="ui-ctl ui-ctl-textarea">

                    <textarea name="bonuses_row[DESCRIPTION]" placeholder="<?= GetMessage("DKY_BONUSES_POPUP_DESCRIPTION_FIELD_TITLE") ?>" class="ui-ctl-element"><?= $arResult['FORM_DATA']['DESCRIPTION'] ?></textarea>
                </div>
            </div>
            <div class="bonuses-form__form-group bonuses-form__form-group_text-center">
                <button onclick="this.classList.add('ui-btn-clock')" type="submit" class="ui-btn ui-btn-success"><?= GetMessage('DKY_BONUSES_POPUP_SAVE_BTN_TITLE') ?></button>
                <button onclick="DKYBonuses.popup.close()" type="button" class="ui-btn ui-btn-success"><?= GetMessage('DKY_BONUSES_POPUP_CANCEL_BTN_TITLE') ?></button>
            </div>
        </form>
    </div>
</template>

<button onclick="DKYBonuses.popup.show()" class="ui-btn ui-btn-success create-bonuses-row-btn"><?= $arParams['POPUP_BTN_TITLE']; ?></button>
<? if (!empty($arResult['ERRORS'])): ?>
    <script>
        BX.ready(function () {
            DKYBonuses.popup.show();
        });
    </script>
<? endif; ?>
