<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Iblock\ElementTable;

class NewsListComponent extends CBitrixComponent
{

    protected function checkModules(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Ошибка загрузки модуля');
        }
    }

    protected function validateParams(): void
    {
        $hasId = !empty($this->arParams['IBLOCK_ID']);
        $hasType = !empty($this->arParams['IBLOCK_TYPE']);

        if ($hasId && $hasType) {
            throw new SystemException('Укажите только один параметр: IBLOCK_ID или IBLOCK_TYPE, не оба.');
        }

        if (!$hasId && !$hasType) {
            throw new SystemException('Необходимо указать IBLOCK_ID или IBLOCK_TYPE.');
        }

        if ($hasId && !is_numeric($this->arParams['IBLOCK_ID'])) {
            throw new SystemException('IBLOCK_ID должен быть числом.');
        }
    }

    protected function getElements(): array
    {
        $filter = ['ACTIVE' => 'Y'];
        $iblocksInfo = [];

        if (!empty($this->arParams['IBLOCK_ID'])) {
            // конкретный инфоблок
            $iblockId = (int)$this->arParams['IBLOCK_ID'];
            $filter['IBLOCK_ID'] = $iblockId;

            $iblockData = CIBlock::GetArrayByID($iblockId);
            if ($iblockData) {
                $iblocksInfo[$iblockId] = $iblockData;
            }
        } else {
            // Все инфоблоки указанного типа
            $iblockIds = [];
            $iblocks = CIBlock::GetList([], [
                'TYPE' => $this->arParams['IBLOCK_TYPE'],
                'ACTIVE' => 'Y'
            ]);

            while ($b = $iblocks->Fetch()) {
                $iblockIds[] = (int)$b['ID'];
                $iblocksInfo[(int)$b['ID']] = $b;
            }

            if (empty($iblockIds)) {
                return [];
            }

            $filter['IBLOCK_ID'] = $iblockIds;
        }

        $result = ElementTable::getList([
            'select' => ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'IBLOCK_ID', 'CODE'],
            'filter' => $filter,
            'order'  => ['SORT' => 'ASC'],
            'limit'  => $this->arParams['NEWS_COUNT'] ?? 10
        ]);

        $items = [];

        while ($row = $result->fetch()) {
            $iblockId = $row['IBLOCK_ID'];

            if (!isset($items[$iblockId])) {
                $items[$iblockId] = [];
            }

            $detailUrlTemplate = $iblocksInfo[$iblockId]['DETAIL_PAGE_URL'] ?? '/';
            $row['DETAIL_PAGE_URL'] = str_replace(
                ['#SITE_DIR#', '#ELEMENT_ID#', '#ELEMENT_CODE#'],
                [SITE_DIR, $row['ID'], $row['CODE'] ?: $row['ID']],
                $detailUrlTemplate
            );

            $items[$iblockId][$row['ID']] = $row;
        }

        return $items;
    }

    public function executeComponent(): void
    {
        try {
            $this->checkModules();
            $this->validateParams();

            if ($this->startResultCache()) {
                $this->arResult['ITEMS'] = $this->getElements();
                $this->includeComponentTemplate();
            }
        } catch (SystemException $e) {
            $this->abortResultCache();
            ShowError($e->getMessage());
        }
    }
}

