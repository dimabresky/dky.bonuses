
if (!window.DKYBonuses) {
    window.DKYBonuses = {
        __timeid: null
    };
}

window.DKYBonuses.hideFindUsersBlock = function () {
    BX('find-users-block').innerHTML = '';
    BX('find-users-block').classList.add('hide');
};

window.DKYBonuses.findUsers = function (e) {
    if (!window.DKYBonuses.__timeid) {

        window.DKYBonuses.__timeid = setTimeout(function () {
            BX.ajax.runComponentAction('dky:admin.bonuses.popup', 'findusers', {
                mode: 'class',
                data: {
                    term: e.target.value
                }
            }).then(res => {
                window.DKYBonuses.__timeid = null;

                window.DKYBonuses.hideFindUsersBlock();
                BX('user-id-input').value = '';
                if (res.data && Array.isArray(res.data) && res.data.length) {
                    BX('find-users-block').classList.remove('hide');
                    res.data.forEach(user => {
                        BX('find-users-block').appendChild(BX.create('div', {
                            attrs: {
                                className: 'find-users-block__user'
                            },
                            dataset: {
                                id: user.ID,
                                name: user.NAME
                            },
                            text: user.NAME,
                            events: {
                                click: function (e) {
                                    BX('find-users-input').value = e.target.dataset.name;
                                    BX('user-id-input').value = e.target.dataset.id;
                                    window.DKYBonuses.hideFindUsersBlock();
                                }
                            }
                        }));
                    });

                } else if (e.target.value) {
                    BX('find-users-block').classList.remove('hide');
                    BX('find-users-block').appendChild(BX.create('div', {
                        attrs: {
                            className: 'find-users-block__no-found-user'
                        },
                        text: 'Clients not found.',
                    }));
                }

            }).catch(() => {
                console.log('find users error');
                window.DKYBonuses.__timeid = null;
            });

        }, 500);
    }

}

BX.ready(function () {
    window.DKYBonuses.popup = new BX.PopupWindow('bonuses-popup', window.body, {

        autoHide: true,
        lightShadow: true,
        closeIcon: true,
        closeByEsc: true,
        overlay: {
            backgroundColor: '#000', opacity: '80'
        }
    });
    window.DKYBonuses.popup.setContent(BX('bonuses-popup-content').innerHTML);

    document.querySelector('.popup-window-overlay').addEventListener('click', function (e) {

        if (!e.target.classList.contains('find-users-block__user')) {
            window.DKYBonuses.hideFindUsersBlock();
        }
    });
    document.querySelector('.popup-window').addEventListener('click', function (e) {

        if (!e.target.classList.contains('find-users-block__user')) {
            window.DKYBonuses.hideFindUsersBlock();
        }
    });

});