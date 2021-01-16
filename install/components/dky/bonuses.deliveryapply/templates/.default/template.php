<br><br>
<?php if ($arResult['APPLYED']): ?>
    <div class="dky-bonuses-delivery-applyed-block">
        <div class="dky-bonuses-delivery-applyed-block__text">Для данной доставки применена оплата бонусами.<br>
            Бонусов: <?= $arResult['BALANCE'] ?><br>
            Стоимость за доставку: <?= $arResult['DELIVERY_BONUSES_COST'] ?> бонусов
        </div>

        <a class="dky-bonuses-delivery-applyed-block__cancel-apply-btn" onclick="BX.DKYBonuses.cancelDeliveryBonusesCost();" href="javascript:void(0)">Отменить</a>

    </div>
<?php else: ?>
    <div class="dky-bonuses-delivery-not-applyed-block">
        <div class="dky-bonuses-delivery-not-applyed-block__text">Для данной доставки доступна оплата бонусами.<br>
            Бонусов: <?= $arResult['BALANCE'] ?><br>
            Стоимость за доставку: <?= $arResult['DELIVERY_BONUSES_COST'] ?> бонусов
        </div>

        <a class="dky-bonuses-delivery-not-applyed-block__apply-btn" onclick="BX.DKYBonuses.applyDeliveryBonusesCost(<?= $arResult['DELIVERY_ID'] ?>);" href="javascript:void(0)">Применить</a>

    </div>
<?php endif; ?>

