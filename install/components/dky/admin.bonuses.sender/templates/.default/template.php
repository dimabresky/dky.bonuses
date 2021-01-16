<?php
Bitrix\Main\UI\Extension::load("ui.buttons");
Bitrix\Main\UI\Extension::load("ui.forms");
Bitrix\Main\UI\Extension::load("ui.alerts");
CJSCore::Init(['ajax', 'popup']);
?>
<template id="bonuses-sender-popup-content">

    <div class="bonuses-popup__form bonuses-form">
        <h2><?= GetMessage('DKY_BONUSES_SENDER_POPUP_TITLE') ?></h2>

        <form onsubmit="return DKYBonuses.sendMessage(event)" action="<?= $APPLICATION->GetCurPageParam() ?>" method="post">

            <input id="bonuses-sender-email" name="email" value="<?= htmlspecialchars($arParams['EMAIL']) ?>" type="hidden">
            <input id="bonuses-sender-hash" name="hash" value="<?= htmlspecialchars($arResult['HASH']) ?>" type="hidden">

            <div class="bonuses-form__form-group">

                <div class="ui-ctl">
                    <select id="bonuses-sender-site" name="site_id" class="ui-ctl-element" >
                        <? foreach ($arResult['SITE_ID'] as $siteid => $name): ?>
                            <option value="<?= $siteid ?>"><?= $name ?></option>
                        <? endforeach ?>
                    </select>

                </div>
            </div>

            <div class="bonuses-form__form-group">

                <div class="ui-ctl">

                    <input id="bonuses-sender-subject" placeholder="<?= GetMessage("DKY_BONUSES_POPUP_SENDER_SUBJECT_TITLE") ?>" name="subject" class="ui-ctl-element" type="text">
                </div>
            </div>



            <div class="bonuses-form__form-group">

                <div class="ui-ctl ui-ctl-textarea">

                    <textarea name="message" id="bonuses-sender-message" placeholder="<?= GetMessage("DKY_BONUSES_POPUP_SENDER_MESSAGE_TITLE") ?>" class="ui-ctl-element"></textarea>
                </div>
            </div>
            <div class="bonuses-form__form-group">

                <div class="ui-ctl">

                    <input class="ui-ctl-element" type="file" id="bonuses-sender-file">
                </div>
            </div>
            <div class="bonuses-form__form-group bonuses-form__form-group_text-center">
                <button id="bonuses-sender-btn" type="submit" class="ui-btn ui-btn-success"><?= GetMessage('DKY_BONUSES_POPUP_SEND_BTN_TITLE') ?></button>
                <button onclick="DKYBonuses.senderPopup.close()" type="button" class="ui-btn ui-btn-success"><?= GetMessage('DKY_BONUSES_POPUP_CANCEL_BTN_TITLE') ?></button>
            </div>
        </form>
    </div>
</template>

<button onclick="DKYBonuses.senderPopup.show()" class="ui-btn ui-btn-success create-bonuses-row-btn"><?= GetMessage('DKY_BONUSES_SENDER_POPUP_SHOW_BTN') ?></button>

<script>
    BX.message({
        DKY_BONUSES_SENDER_POPUP_SUCCESS: "<?= GetMessage("DKY_BONUSES_SENDER_POPUP_SUCCESS") ?>",
        DKY_BONUSES_SENDER_POPUP_ERROR: "<?= GetMessage("DKY_BONUSES_SENDER_POPUP_ERROR") ?>"
    });
</script>

