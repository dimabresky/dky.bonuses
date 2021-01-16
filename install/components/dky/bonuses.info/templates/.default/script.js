
document.addEventListener('DOMContentLoaded', function () {


    BX.Vue.create({
        el: "#bonuses-info",
        data: {
            infoTab: true,
            historyListTab: false,
            info: null,
            historyList: null,
            charityComment: ''

        },
        mounted() {
            BX.ajax.runComponentAction('dky:bonuses.info', 'info', {
                mode: 'class',
                data: {}
            }).then(res => {

                this.info = res.data;

            });
            BX.ajax.runComponentAction('dky:bonuses.info', 'historyList', {
                mode: 'class',
                data: {}
            }).then(res => {

                this.historyList = res.data;

            });
        },
        methods: {
            charityTitle(title) {
                if (!this.info) {
                    return '';
                }
                return title.replace('#CHARITY_SUM#', this.info.CHARITY);
            },
            sendCharityRequest(e, confirmText, errorText, successText) {
                if (confirm(confirmText)) {

                    e.target.innerText += '...';
                    BX.ajax.runComponentAction('dky:bonuses.info', 'charityRequest', {
                        mode: 'class',
                        data: {comment: this.charityComment}
                    }).then(() => {

                        alert(successText);
                        location.reload();
                    }).catch(() => {
                        alert(errorText);
                        e.target.innerText = e.target.innerText.replace('...', '');
                    });

                }
            }
        }

    });
});

