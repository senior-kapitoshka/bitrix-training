<?php

namespace Only\Site\Handlers;
use Bitrix\Main\Loader;


class Iblock
{
    public function addLog()
    {
        
        if (self::$handlerDisallow) return;
        self::$handlerDisallow = true;


        if (!Loader::includeModule('iblock')) return;

        $IBLOCK_ID = self::findIBlockID(self::$IBLOCK_CODE);
        if (!$IBLOCK_ID) return;


        if ($arFields["IBLOCK_ID"] == $IBLOCK_ID) return;

        $arIBlock = CIBlock::GetByID($arFields["IBLOCK_ID"])->Fetch();
        if (!$arIBlock) return;

        //какой раздел LOG 
        $sectionID = self::findOrCreateSection( $IBLOCK_ID,
                $arIBlock["NAME"] . "_" . $arIBlock["CODE"]
            );

        //данные для элемента LOG 
        $arFieldsElement = [
            "IBLOCK_ID"         => $IBLOCK_ID,
            "IBLOCK_SECTION_ID" => $sectionID,
            "NAME"              => $arFields["ID"],
            "ACTIVE"            => 'Y',
            "ACTIVE_FROM"       => date('d.m.Y'),
            "PREVIEW_TEXT"      => self::generatePreviewText($arFields),
        ];

        //проверка наличия элемента в LOG 
        $element = new CIBlockElement;
        $existingElement = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $IBLOCK_ID, 'NAME' => $arFields["ID"]],
            false,
            false,
            ['ID']
        )->Fetch();


        if ($existingElement) {
            if ($element->Update($existingElement["ID"], $arFieldsElement)) {
                AddMessage2Log("Элемент с ID-{$existingElement['ID']} обновлен.");
            } else {
                AddMessage2Log("Ошибка обновления элемента: " . $element->LAST_ERROR);
            }
        } else {
            if ($elementID = $element->Add($arFieldsElement)) {
                AddMessage2Log("Добавлен новый элемент с ID-{$elementID}.");
            } else {
                AddMessage2Log("Ошибка добавления элемента: " . $element->LAST_ERROR);
            }
        }

            //заново запускаем обработчик 
        self::$handlerDisallow = false;

    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

}
