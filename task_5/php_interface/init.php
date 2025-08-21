<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::includeModule('iblock');

define('LOGGER_CODE', 'LOG');

if (!Loader::includeModule('dev.site')) {
    return;
}

Loader::registerAutoLoadClasses('dev.site', [
    'Only\Site\Handlers\Iblock' => 'lib/Handlers/IBlock.php',
    'Only\Site\Agents\Iblock'   => 'lib/Agents/Iblock.php',
]);



EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    ['Only\Site\Handlers\Iblock', 'addLog']
);
EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    ['Only\Site\Handlers\Iblock', 'addLog']
);

// регистрируем агент
$agentName = "Only\\Site\\Agents\\Iblock::clearOldLogs();";
if (!\CAgent::GetList([], ['NAME' => $agentName])->Fetch()) {
    \CAgent::AddAgent(
        $agentName,
        "dev.site",
        "N",
        3600,
        "",
        "Y",
        ConvertTimeStamp(time() + 3600, "FULL")
    );
}
