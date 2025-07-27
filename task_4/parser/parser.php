<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}

\Bitrix\Main\Loader::includeModule('iblock');

$IBLOCK_ID = 5;
$CSV_PATH = $_SERVER['DOCUMENT_ROOT'] . '/local/parser/vacancy.csv';
if (!file_exists($CSV_PATH)) {
    die("Файл CSV не найден по пути: $CSV_PATH");
}

$el = new CIBlockElement;
$ibpenum = new CIBlockPropertyEnum;

$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->Fetch()) {
    CIBlockElement::Delete($element['ID']);
}

function GetAllPropertyValues($IBLOCK_ID)
{
    $arProps = [];
    $rsProp = CIBlockPropertyEnum::GetList([
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ], [
        'IBLOCK_ID' => $IBLOCK_ID
    ]);
    while ($arProp = $rsProp->Fetch()) {
        $key = trim($arProp['VALUE']);
        $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
    }
    return $arProps;
}

$arProps = GetAllPropertyValues($IBLOCK_ID);

if (($handle = fopen($CSV_PATH, "r")) !== false) {
    $row = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $row++;
        if ($row === 1) continue;

        $PROP['ACTIVITY'] = $data[9];
        $PROP['FIELD'] = $data[11];
        $PROP['OFFICE'] = $data[1];
        $PROP['EMAIL'] = $data[12];
        $PROP['LOCATION'] = $data[2];
        $PROP['TYPE'] = $data[8];
        $PROP['SALARY_TYPE'] = '';
        $PROP['SALARY_VALUE'] = $data[7];
        $PROP['REQUIRE'] = $data[4];
        $PROP['DUTY'] = $data[5];
        $PROP['CONDITIONS'] = $data[6];
        $PROP['SCHEDULE'] = $data[10];
        $PROP['DATE'] = date('d.m.Y');

        foreach ($PROP as $key => $val) {
            if (is_array($val)) {
                foreach ($val as &$item) {
                    $item = trim(str_replace('\n', '', $item));
                }
                unset($item);
            } else {
                $val = trim(str_replace('\n', '', $val));
            }
        
            if (is_string($val) && (stripos($val, '•') !== false || stripos($val, '-') !== false)) {
                $items = explode('•', $val);
                array_splice($items, 0, 1); 
                foreach ($items as &$str) {
                    $str = trim($str);
                }
                unset($str);
                $val = $items;
            }
        
            if ($arProps[$key]) {
                if ($key === 'OFFICE' && is_string($val)) {
                    $val = strtolower($val);
                    if ($val == 'центральный офис') {
                        $val .= 'свеза ' . $data[2];
                    } elseif ($val == 'лесозаготовка') {
                        $val = 'свеза ресурс ' . $val;
                    } elseif ($val == 'свеза тюмень') {
                        $val = 'свеза тюмени';
                    }
        
                    $arSimilar = [];
                    foreach ($arProps[$key] as $propKey => $propVal) {
                        $arSimilar[similar_text($val, $propKey)] = $propVal;
                    }
                    ksort($arSimilar);
                    $val = array_pop($arSimilar);
        
                } elseif ($key !== 'SALARY_TYPE') {
                    if (is_array($val)) {
                        $arValue = $val;
                    } elseif (is_string($val)) {
                        $arValue = preg_split('/[\/,]/', $val);
                    } else {
                        $arValue = [];
                    }
        
                    foreach ($arProps[$key] as $propKey => $propVal) {
                        foreach ($arValue as $item) {
                            $item = trim($item);
                            if ($item === '') continue;
                            if (stripos($propKey, $item) !== false || similar_text($propKey, $item) > 50) {
                                $val = $propVal;
                                break 2;
                            }
                        }
                    }
                }
            }
        
            $PROP[$key] = $val;
        }
        

        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $data[3],
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];

        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
        } else {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    fclose($handle);
}
