<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DkyAdminBonusesSender extends CBitrixComponent implements Controllerable {

    const SALT = "kasdljfkalsjd2341=2312lkfskdjfasdf";

    /**
     * @return array
     */
    function configureActions(): array {
        return [
            'sendMessage' => [
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

        $this->arParams['EMAIL'] = $this->arParams['EMAIL'] ?: '';

        if ($this->arParams['EMAIL']) {

            $this->arResult['HASH'] = $this->hash($this->arParams['EMAIL']);

            $dbSites = CSite::GetList($by = "id", $order = "asc");

            $this->arResult['SITE_ID'] = [];
            while ($arSite = $dbSites->Fetch()) {
                $this->arResult['SITE_ID'][$arSite['ID']] = "[{$arSite['ID']}]{$arSite['NAME']}";
            }

            $this->includeComponentTemplate();
        }
    }

    function sendMessageAction($email, $site, $hash, $subject, $message, $file) {

        $email = $email ?: '';

        if ($hash === $this->hash($email)) {
            $message = $message ? strip_tags(trim($message)) : '';

            if (!$message) {
                throw new Exception(Loc::getMessage('DKY_BONUSES_SENDER_EMPTY_MESSAGE'));
            }

            if ($email && check_email($email)) {

                $arEmailFields = [
                    "EVENT_NAME" => "BONUSES_NOTIFICATIONS",
                    "LID" => trim(strip_tags($site)),
                    "C_FIELDS" => array(
                        "SUBJECT" => strip_tags(trim($subject)) ?: Loc::getMessage("DKY_BONUSES_SENDER_MESSAGE_SUBJECT"),
                        "EMAIL_TO" => $email,
                        "TEXT" => $message
                    ),
                ];

                if ($filePath = $this->saveFile($file)) {
                    $arEmailFields["FILE"] = [$filePath];
                }
                Event::send($arEmailFields);
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @return string
     */
    function hash($email) {
        return md5(self::SALT . $email);
    }

    /**
     * @param array $file
     * @return string
     */
    function saveFile($file) {
        if (
                $file && is_array($file) &&
                $file['base64'] &&
                $file['name']
        ) {

            list(, $base64) = explode(',', $file['base64']);
            if ($base64 && ($fileContent = base64_decode($base64))) {
                $tmpDir = $_SERVER['DOCUMENT_ROOT'] . "/upload/tmp";

                if (!file_exists($tmpDir)) {
                    (new CBXVirtualIo)->CreateDirectory($tmpDir);
                }
                $filePath = $tmpDir . '/' . $file['name'];
                file_put_contents($filePath, $fileContent);
                return $filePath;
            }
        }
        return '';
    }

}
