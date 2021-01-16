<?php CJSCore::Init(['ajax']) ?>
<div class="col-md-12 bonuses-discount">
    <? if (!$arResult['APPLYED_BONUSES_COUPON']): ?>
        <div class="col-md-3">
            <strong class="bonuses-discount__title"><?= GetMessage('DKY_BONUSES_DISCOUNT_TITLE', ['#BONUSES#' => $arResult['BALANCE']['BONUSES'], '#DISCOUNT#' => $arResult['BALANCE']['DISCOUNT']]) ?></strong><br>
            <span class="bonuses-discount__notify"><?= GetMessage('DKY_BONUSES_DISCOUNT_NOTIFY', ['#DISCOUNT#' => $arResult['MAX_DISCOUNT']]) ?></span>
        </div>
        <div class="col-md-9">
            <button onclick="BX.DKYBonuses.applyDiscount(); event.target.innerText += '...'" class="btn btn-lg btn-success btn-success_yellow"><?= GetMessage('DKY_BONUSES_DISCOUNT_BTN') ?></button>
        </div>
    <? else: ?>
        <div class="col-md-4">
            <strong class="bonuses-discount__title"><?= GetMessage('DKY_BONUSES_DISCOUNT_APPLYED_TITLE', ['#BONUSES#' => $arResult['BALANCE']['BONUSES'], '#APPLYED#' => $arResult['APPLYED_BONUSES_COUPON']['BONUSES']]) ?></strong><br>
            <span class="bonuses-discount__notify"><?= GetMessage('DKY_BONUSES_DISCOUNT_APPLYED_NOTIFY', ['#DISCOUNT#' => $arResult['APPLYED_BONUSES_COUPON']['DISCOUNT']]) ?></span>
        </div>
        <div class="col-md-8">
            <button onclick="BX.DKYBonuses.cancelDiscount(); event.target.innerText += '...'" class="btn btn-lg btn-danger"><?= GetMessage('DKY_BONUSES_DISCOUNT_CANCEL_BTN') ?></button>
        </div>
    <? endif ?>
</div>

<script>
    BX.message({DKY_BONUSES_DISCOUNT_APPLYED_ERROR: '<?= GetMessage('DKY_BONUSES_DISCOUNT_APPLYED_ERROR') ?>'});
    BX.message({DKY_BONUSES_DISCOUNT_CANCEL_ERROR: '<?= GetMessage('DKY_BONUSES_DISCOUNT_CANCEL_ERROR') ?>'});
</script>