<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

\Bitrix\Main\UI\Extension::load("ui.vue");
?>

<div class="bonuses-info" id="bonuses-info">
    <div class="bonuses-info__tabs tabs">
        <div @click="infoTab=true;historyListTab=false" :class="infoTab? 'tabs__tab tab tabs__tab_active' : 'tabs__tab tab'"><?= GetMessage('DKY_BONUSES_INFO_TAB') ?></div>
        <div @click="historyListTab=true;infoTab=false" :class="historyListTab ? 'tabs__tab tab tabs__tab_active' : 'tabs__tab tab'"><?= GetMessage('DKY_BONUSES_HISTORY_LIST_TAB') ?></div>
    </div>

    <div class="bonuses-info__tabs-content tabs-content">
        <div  :class="infoTab ? 'tabs-content__content tabs-content__content_active content' : 'tabs-content__content content'">
            <div v-if="!info" class="content__loading"><?= GetMessage('DKY_BONUSES_LOADING_TAB') ?></div>
            <div v-else class="content__info info">
                <table class="info__table table">
                    <tr>
                        <td class="table__cell">
                            <?= GetMessage('DKY_BONUSES_BONUSES_TITLE'); ?>
                        </td>
                        <td class="table__cell table__cell_yellow">
                            {{info.BONUSES}}
                        </td>
                    </tr>
                    <tr>
                        <td class="table__cell">
                            <?= GetMessage('DKY_BONUSES_DISCOUNT_TITLE'); ?>
                        </td>
                        <td class="table__cell table__cell_yellow">
                            {{info.DISCOUNT}}%
                        </td>
                    </tr>
                </table>
                <div class="info__notify"><?= GetMessage('DKY_BONUSES_DISCOUNT_NOTIFY'); ?></div>
                <div class="info__line" ></div>
                <div v-if="info.DELIVERIES.length">
                    <table class="info__table table">
                        <tr v-for="delivery in info.DELIVERIES">
                            <td class="table__cell">
                                {{delivery.NAME}}
                            </td>
                            <td :class="delivery.AVAIL ? 'table__cell table__cell_color-green' : 'table__cell table__cell_color-red'">
                                {{delivery.AVAIL ? '<?= GetMessage('DKY_BONUSES_DELIVERY_AVAIL_TITLE') ?>': '<?= GetMessage('DKY_BONUSES_DELIVERY_NOT_AVAIL_TITLE') ?>'}}
                        </td>
                    </tr>
                </table>
                <div class="info__notify"><?= GetMessage('DKY_BONUSES_DELIVERY_NOTIFY'); ?></div>
                <div class="info__line" ></div>
            </div>
            <table v-if="info.CHARITY>0" class="info__table table">
                <tr>
                    <td v-html="charityTitle('<?= GetMessage('DKY_BONUSES_CHARITY_TITLE'); ?>')" class="table__cell"></td>
                    <td class="table__cell">
                        <button @click="sendCharityRequest(event, '<?= GetMessage('DKY_BONUSES_CHARITY_CONFIRM') ?>', '<?= GetMessage('DKY_BONUSES_CHARITY_ERROR') ?>', '<?= GetMessage('DKY_BONUSES_CHARITY_SUCCESS') ?>')" class="btn-success btn"><?= GetMessage('DKY_BONUSES_CHARITY_BTN') ?></button>
                    </td>
                </tr>

            </table>
            <div v-if="info.CHARITY>0" class="info__notify"><?= GetMessage('DKY_BONUSES_CHARITY_NOTIFY'); ?></div>
            <textarea v-if="info.CHARITY>0" class="charity-comment" v-model="charityComment"></textarea>
        </div>
    </div>
    <div :class="historyListTab ? 'tabs-content__content tabs-content__content_active content' : 'tabs-content__content content'">
        <div v-if="!historyList" class="content__loading"><?= GetMessage('DKY_BONUSES_LOADING_TAB') ?></div>
        <div v-else class="content__history-list history-list">
            <table class="table">
                <tr>
                    <th class="table__noborder"><?= GetMessage('DKY_BONUSES_DATE_CELL')?></th>
                    <th class="table__noborder"><?= GetMessage('DKY_BONUSES_BONUSES_CELL')?></th>
                    <th class="table__noborder"><?= GetMessage('DKY_BONUSES_COMMENT_CELL')?></th>
                </tr>
                <tr v-for="row in historyList">
                    <td >{{row.DATE}}</td>
                    <td >{{row.BONUSES}}</td>
                    <td >{{row.DESCRIPTION}}</td>
                </tr>

            </table>
        </div>
    </div>
</div>
</div>

