<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DkyAdminBonusesPopup extends CBitrixComponent implements Controllerable {

    /**
     * @return array
     */
    function configureActions(): array {
        return [
            'findusers' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                            array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf()
                ]
            ]
        ];
    }

    function executeComponent() {

        Bitrix\Main\Loader::includeModule('dky.bonuses');

        $this->arParams['USER_ID'] = intval($this->arParams['USER_ID']);

        $this->processRequest();

        $this->setDefFormData();

        $this->includeComponentTemplate();
    }

    function setDefFormData() {
        $arBonusesRow = $this->request->getPost('bonuses_row');
        $this->arResult['FORM_DATA'] = [
            'BONUSES' => $arBonusesRow['BONUSES'] ? htmlspecialchars($arBonusesRow['BONUSES']) : '',
            'USER' => [
                'ID' => '',
                'NAME' => ''
            ],
            'DESCRIPTION' => $arBonusesRow['DESCRIPTION'] ? htmlspecialchars($arBonusesRow['DESCRIPTION']) : ''
        ];
        
        if ($arBonusesRow['USER_ID']>0) {
           
            $res = $this->getUsers(['ID' => $arBonusesRow['USER_ID']]);
            if (!empty($res)) {
                $this->arResult['FORM_DATA']['USER']['ID'] = $res[0]['ID'];
                $this->arResult['FORM_DATA']['USER']['NAME'] = $res[0]['NAME'];
            }
        }
        
    }

    function processRequest() {

        global $APPLICATION;

        $this->arResult['ERRORS'] = [];

        $arBonusesRow = [];

        if (
                $this->request->isPost() &&
                is_array($this->request->getPost('bonuses_row')) &&
                !empty($this->request->getPost('bonuses_row')) &&
                check_bitrix_sessid()
        ) {
            $arBonusesRow = $this->request->getPost('bonuses_row');
            if ($this->arParams['USER_ID']) {

                if ($this->arParams['USER_ID'] != $arBonusesRow['USER_ID']) {
                    return;
                }
            } elseif (\dky\BonusesTable::getList([
                        'filter' => [
                            'USER_ID' => $arBonusesRow['USER_ID']
                        ],
                        'select' => ['ID']
                    ])->fetch()) {
                $this->arResult['ERRORS'][] = Loc::getMessage('DKY_BONUSES_ROW_ALREADY_EXISTS');
                return;
            }

            $arFields = [
                'BONUSES' => $arBonusesRow['BONUSES'],
                'USER_ID' => $arBonusesRow['USER_ID'],
                'DATE' => Bitrix\Main\Type\DateTime::createFromTimestamp(time()),
                'DESCRIPTION' => strip_tags(trim($arBonusesRow['DESCRIPTION']))
            ];
            $res = \dky\BonusesHistoryTable::add($arFields);
            if ($res->isSuccess()) {
                LocalRedirect($APPLICATION->GetCurPageParam());
            }

            $this->arResult['ERRORS'] = $res->getErrorMessages();
        }
    }

    /**
     * @param string $term
     * @return array
     */
    function findusersAction($term) {

        $result = [];
        if ($term) {
            $filter = [
                [
                    'LOGIC' => 'OR',
                    ['NAME' => "%$term%"],
                    ['LAST_NAME' => "%$term%"],
                    ['SECOND_NAME' => "%$term%"],
                    ['EMAIL' => "%$term%"],
                    ['LOGIN' => "%$term%"]
                ]
            ];

            $result = $this->getUsers($filter);
        }

        return $result;
    }
    
    /**
     * @param array $filter
     * @return array
     */
    protected function getUsers(array $filter) {
        $result = [];
        $dbUsers = UserTable::getList(['order' => ['NAME' => 'ASC'], 'filter' => $filter, 'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'LOGIN']]);
        while ($arUser = $dbUsers->fetch()) {
            $userName = '';
            if ($arUser['NAME']) {
                $userName = $arUser['NAME'];
                if ($arUser['SECOND_NAME']) {
                    $userName .= " {$arUser['SECOND_NAME']}";
                }
                if ($arUser['LAST_NAME']) {
                    $userName .= " {$arUser['LAST_NAME']}";
                }
            } else {
                $userName = $arUser['LOGIN'];
            }

            $result[] = [
                'ID' => $arUser['ID'],
                'NAME' => "[{$arUser['ID']}]" . $userName
            ];
        }
        
        return $result;
    }

}
