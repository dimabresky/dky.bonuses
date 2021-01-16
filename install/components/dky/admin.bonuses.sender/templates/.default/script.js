
if (!window.DKYBonuses) {
    window.DKYBonuses = {};
}

window.DKYBonuses.__sendSenderForm = function (data) {
    BX('bonuses-sender-btn').classList.add('ui-btn-clock');
    BX.ajax.runComponentAction('dky:admin.bonuses.sender', 'sendMessage', {
        mode: 'class',
        data: data
    }).then(res => {

        if (res.data) {
            alert(BX.message('DKY_BONUSES_SENDER_POPUP_SUCCESS'));
        }

        BX('bonuses-sender-subject').value = '';
        BX('bonuses-sender-message').value = '';
        BX('bonuses-sender-file').value = '';

        BX('bonuses-sender-btn').classList.remove('ui-btn-clock');
        window.DKYBonuses.senderPopup.close();

    }).catch(() => {

        BX('bonuses-sender-btn').classList.remove('ui-btn-clock');
        alert(BX.message('DKY_BONUSES_SENDER_POPUP_ERROR'));
    });
}

BX.ready(function () {
    window.DKYBonuses.senderPopup = new BX.PopupWindow('bonuses-sender-popup', window.body, {

        autoHide: true,
        lightShadow: true,
        closeIcon: true,
        closeByEsc: true,
        overlay: {
            backgroundColor: '#000', opacity: '80'
        }
    });
    window.DKYBonuses.senderPopup.setContent(BX('bonuses-sender-popup-content').innerHTML);

    window.DKYBonuses.sendMessage = function (e) {
        const data = {
            email: BX('bonuses-sender-email').value,
            hash: BX('bonuses-sender-hash').value,
            subject: BX('bonuses-sender-subject').value,
            message: BX('bonuses-sender-message').value,
            site: BX('bonuses-sender-site').value,
            file: {
                base64: '',
                name: ''
            }
        };

        if (BX('bonuses-sender-file').files && BX('bonuses-sender-file').files[0]) {
            const reader = new FileReader();
            reader.readAsDataURL(BX('bonuses-sender-file').files[0]);
            reader.onload = () => {
                data.file.base64 = reader.result;
                data.file.name = BX('bonuses-sender-file').files[0].name;
                window.DKYBonuses.__sendSenderForm(data);
            };
        } else {
            window.DKYBonuses.__sendSenderForm(data);
        }



        return false;
    };

});