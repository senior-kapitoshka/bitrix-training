<?php

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class custom_cprop extends CModule
{
    var $MODULE_ID  = 'custom.cprop';

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';

        $this->MODULE_ID = 'custom.cprop';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('CPROP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('CPROP_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('CPROP_PARTNER_NAME');
        $this->PARTNER_URI = 'https://phpdev.org';

        $this->FILE_PREFIX = 'cprop';
        $this->MODULE_FOLDER = str_replace('.', '_', $this->MODULE_ID);
        $this->FOLDER = 'bitrix';

        $this->INSTALL_PATH_FROM = '/' . $this->FOLDER . '/modules/' . $this->MODULE_ID;
    }

    function isVersionD7()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;
        if($this->isVersionD7())
        {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();

            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage('CPROP_INSTALL_ERROR_VERSION'));
        }
    }

    function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
    }


    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        return true;
    }

    function installFiles()
    {
        return true;
    }

    function uninstallFiles()
    {
        return true;
    }


    protected function getEvents(): array
    {
        /*
        OnIBlockPropertyBuildList — это системное событие в Битрикс, которое вызывается при инициализации типов свойств инфоблоков.
        чтобы добавить кастомный тип свойства => подписаться на событие OnIBlockPropertyBuildList
        */
        return [
            [
                'FROM_MODULE' => 'iblock',
                'EVENT' => 'OnIBlockPropertyBuildList',
                'TO_METHOD' => 'GetUserTypeDescription'
            ]
        ];
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $classHandler = 'ComplexProperty';
        foreach ($this->getEvents() as $event) {
            $eventManager->registerEventHandler(
                $event['FROM_MODULE'],
                $event['EVENT'],
                $this->MODULE_ID,
                $classHandler,
                $event['METHOD']
            );
        }

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $classHandler = 'ComplexProperty';
        $arEvents = $this->getEvents();
        foreach($arEvents as $arEvent){
            $eventManager->unregisterEventHandler(
                $arEvent['FROM_MODULE'],
                $arEvent['EVENT'],
                $this->MODULE_ID,
                $classHandler,
                $arEvent['TO_METHOD']
            );
        }

        return true;
    }
}