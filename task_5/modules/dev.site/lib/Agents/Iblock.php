<?php

namespace Only\Site\Agents;
use Bitrix\Main\Loader;


class Iblock
{
    public static function clearOldLogs()
    {
        if (!Loader::includeModule('iblock')) return __METHOD__ . '();';

        $IBLOCK_CODE = 'LOG';

        // id инфоблока по коду
        $IBLOCK_ID = CIBlock::GetList([], ['CODE' => $IBLOCK_CODE])->Fetch()['ID'] ?? null;
        if (!$IBLOCK_ID) {
            AddMessage2Log("Инфоблок с кодом {$IBLOCK_CODE} не найден.");
            return __METHOD__ . '();';
        }

        //список элементов иб
        $res = CIBlockElement::GetList(
            ['ACTIVE_FROM' => 'DESC'],
            ['IBLOCK_ID' => $IBLOCK_ID],
            false,
            false,
            ['ID']
        );

        $elements = [];
        // собираем id
        while ($item = $res->Fetch()) {
            $elements[] = $item['ID'];
        }

        if (count($elements) <= 10) return __METHOD__ . '();';

        // удаляем остальные по id
        foreach (array_slice($elements, 10) as $id) {
            if (CIBlockElement::Delete($id))
                AddMessage2Log("Запись с ID-{$id} удалена.");  
            else
                AddMessage2Log("Ошибка удаления записи с ID-{$id}"); 
        }

        return __METHOD__ . '();';
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
