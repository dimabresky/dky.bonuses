
BX.namespace("DKYBonuses");

BX.DKYBonuses.submitOrderForm = () => {
    if (typeof submitForm === 'function') {
        submitForm();
    } else if (typeof BX.Sale.OrderAjaxComponent.sendRequest === 'function') {
        try {
            if (BX('bx-soa-order-form')) {
                BX.Sale.OrderAjaxComponent.sendRequest();
            }
        } catch (e) {
            console.warn(e.message);
        }

    }

}

BX.DKYBonuses.applyDeliveryBonusesCost = (deliveryid) => {

    BX.ajax.runComponentAction('dky:bonuses.deliveryapply', 'applyDeliveryBonuses', {
        mode: 'class',
        data: {deliveryid: deliveryid}
    }).then(res => {

        BX.DKYBonuses.submitOrderForm();
    }).catch(() => {

        alert(BX.message('DKY_BONUSES_DELIVERY_APPLYED_ERROR'));
    });
}

BX.DKYBonuses.cancelDeliveryBonusesCost = () => {

    BX.ajax.runComponentAction('dky:bonuses.deliveryapply', 'cancelDeliveryBonuses', {
        mode: 'class',
        data: {}
    }).then(res => {

        BX.DKYBonuses.submitOrderForm();

    }).catch(() => {

        alert(BX.message('DKY_BONUSES_DELIVERY_CANCEL_ERROR'));
    });
}