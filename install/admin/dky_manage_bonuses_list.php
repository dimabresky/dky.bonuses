<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/modules/dky.bonuses/admin/manage_bonuses_list.php')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/local/modules/dky.bonuses/admin/manage_bonuses_list.php';
} else {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/dky.bonuses/admin/manage_bonuses_list.php';
}