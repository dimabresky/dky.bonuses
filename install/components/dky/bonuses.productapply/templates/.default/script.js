BX.namespace("DKYBonuses");

BX.DKYBonuses.applyDiscount = () => {

    BX.ajax.runComponentAction('dky:bonuses.productapply', 'applyBonusesDiscount', {
        mode: 'class',
        data: {}
    }).then(res => {

        location.reload();

    }).catch(() => {
        console.log('applyBonusesDiscount error');
        alert(BX.message('DKY_BONUSES_DISCOUNT_APPLYED_ERROR'));
    });
}

BX.DKYBonuses.cancelDiscount = () => {

    BX.ajax.runComponentAction('dky:bonuses.productapply', 'cancelBonusesDiscount', {
        mode: 'class',
        data: {}
    }).then(res => {

        location.reload();

    }).catch(() => {
        console.log('cancelBonusesDiscount error');
        alert(BX.message('DKY_BONUSES_DISCOUNT_CANCEL_ERROR'));
    });
}