<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ComplexProperty
{
    public static function GetUserTypeDescription()
    {
        return [
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => "C",
            "DESCRIPTION" => Loc::getMessage('CPROP_DESC'),
            "GetPropertyFieldHtml" => [__CLASS__, "GetPropertyFieldHtml"],
            "GetPropertyFieldHtmlMulty" => [__CLASS__, "GetPropertyFieldHtmlMulty"],
            "ConvertToDB" => [__CLASS__, "ConvertToDB"],
            "ConvertFromDB" => [__CLASS__, "ConvertFromDB"],
            "PrepareSettings" => [__CLASS__, "PrepareSettings"],
            "GetSettingsHTML" => [__CLASS__, "GetSettingsHTML"],
        ];
        
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $hideText = Loc::getMessage('CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('CPROP_CLEAR_TEXT');

        self::showCss();
        self::showJs();

        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']); // <-- добавил
        $arValue = [];
        if (!empty($value['VALUE'])) {
            $arValue = json_decode($value['VALUE'], true);
            if (!is_array($arValue)) $arValue = [];
        }

        $html = '';
        $html .= '<div class="mf-gray"><a class="cl mf-toggle">'.$hideText.'</a>';
        if($arProperty['MULTIPLE'] === 'Y'){
            $html .= ' | <a class="cl mf-delete">'.$clearText.'</a></div>';
        }

        $html .= '<table class="mf-fields-list active">';
        foreach ($arFields as $code => $arItem) {
            switch ($arItem['TYPE']) {
                case 'string':
                    $html .= self::showString($code, $arItem['TITLE'], $arValue, $strHTMLControlName);
                    break;
                case 'file':
                    $html .= self::showFile($code, $arItem['TITLE'], $arValue, $strHTMLControlName);
                    break;
                case 'text':
                    $html .= self::showTextarea($code, $arItem['TITLE'], $arValue, $strHTMLControlName);
                    break;
                case 'date':
                    $html .= self::showDate($code, $arItem['TITLE'], $arValue, $strHTMLControlName);
                    break;
                case 'element':
                    $html .= self::showBindElement($code, $arItem['TITLE'], $arValue, $strHTMLControlName);
                    break;
                case 'html':
                    $html .= self::showHtmlEditor($code, $arItem['TITLE'], $arValue, $strHTMLControlName);
                    break;
            }
        }
        $html .= '</table>';

        return $html;
    }


    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $arValue = json_decode($value['VALUE'], true);
        if (!is_array($arValue)) return '';
        $res = '';
        foreach ($arValue as $field => $val)
        {
            $res .= '<div><b>'.$field.':</b> '.$val.'</div>';
        }
        return $res;
    }

    ///////////

     public function ConvertToDB($arProperty, $arValue)
    {
        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        foreach($arValue['VALUE'] as $code => $value){
            if($arFields[$code]['TYPE'] === 'file'){
                $arValue['VALUE'][$code] = self::prepareFileToDB($value);
            }
        }

        $isEmpty = true;
        foreach ($arValue['VALUE'] as $v){
            if(!empty($v)){
                $isEmpty = false;
                break;
            }
        }

        if($isEmpty === false){
            $arResult['VALUE'] = json_encode($arValue['VALUE']);
        }
        else{
            $arResult = ['VALUE' => '', 'DESCRIPTION' => ''];
        }

        return $arResult;
    }

    private static function prepareFileToDB($arValue)
    {
        $result = false;

        if(!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])){
            CFile::Delete($arValue['OLD']);
        }
        else if(!empty($arValue['OLD'])){
            $result = $arValue['OLD'];
        }
        else if(!empty($arValue['name'])){
            $result = CFile::SaveFile($arValue, 'vote');
        }

        return $result;
    }
    ///////////////////
    public function ConvertFromDB($arProperty, $arValue)
    {
        $return = array();

        if(!empty($arValue['VALUE'])){
            $arData = json_decode($arValue['VALUE'], true);

            foreach ($arData as $code => $value){
                $return['VALUE'][$code] = $value;
            }

        }
        return $return;
    }

    private static function showString($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td><input type="text" value="'.$v.'" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
                </tr>';

        return $result;
    }

    ///////////////////////////

    private static function showHtmlEditor($code, $title, $arValue, $strHTMLControlName)
    {
        $value = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $name = $strHTMLControlName["VALUE"].'['.$code.']';

        ob_start();
        \CFileMan::AddHTMLEditorFrame(
            $name,
            $value,
            $name."_TYPE",
            strlen($value) ? "html" : "text",
            [
                'height' => 200,
                'width' => '100%'
            ]
        );
        return '<tr><td align="right">'.$title.': </td><td>'.ob_get_clean().'</td></tr>';
    }


    ////////////////////

    private static function showFile($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        if(!empty($arValue['VALUE'][$code]) && !is_array($arValue['VALUE'][$code])){
            $fileId = $arValue['VALUE'][$code];
        }
        else if(!empty($arValue['VALUE'][$code]['OLD'])){
            $fileId = $arValue['VALUE'][$code]['OLD'];
        }
        else{
            $fileId = '';
        }

        if(!empty($fileId))
        {
            $arPicture = CFile::GetByID($fileId)->Fetch();
            if($arPicture)
            {
                $strImageStorePath = COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/'.$strImageStorePath.'/'.$arPicture['SUBDIR'].'/'.$arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if(in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])){
                    $content = '<img src="'.$sImagePath.'">';
                }
                else{
                    $content = '<div class="mf-file-name">'.$arPicture['FILE_NAME'].'</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>'.$content.'<br>
                                        <div>
                                            <label><input name="'.$strHTMLControlName['VALUE'].'['.$code.'][DEL]" value="Y" type="checkbox"> '. Loc::getMessage("CPROP_FILE_DELETE") . '</label>
                                            <input name="'.$strHTMLControlName['VALUE'].'['.$code.'][OLD]" value="'.$fileId.'" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>                      
                        </td>
                    </tr>';
            }
        }
        else{
            $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td><input type="file" value="" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
                </tr>';
        }

        return $result;
    }

    public static function showTextarea($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                    <td align="right" valign="top">'.$title.': </td>
                    <td><textarea rows="8" name="'.$strHTMLControlName['VALUE'].'['.$code.']">'.$v.'</textarea></td>
                </tr>';

        return $result;
    }

    public static function showDate($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                        <td align="right" valign="top">'.$title.': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text" name="'.$strHTMLControlName['VALUE'].'['.$code.']" size="23" value="'.$v.'">
                                            <span class="adm-calendar-icon"
                                                  onclick="BX.calendar({node: this, field:\''.$strHTMLControlName['VALUE'].'['.$code.']\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

        return $result;
    }

    public static function showBindElement($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $elUrl = '';
        if(!empty($v)){
            $arElem = \CIBlockElement::GetList([], ['ID' => $v],false, ['nPageSize' => 1], ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if(!empty($arElem)){
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElem['IBLOCK_ID'].'&ID='.$arElem['ID'].'&type='.$arElem['IBLOCK_TYPE_ID'].'">'.$arElem['NAME'].'</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">'.$title.': </td>
                    <td>
                        <input name="'.$strHTMLControlName['VALUE'].'['.$code.']" id="'.$strHTMLControlName['VALUE'].'['.$code.']" value="'.$v.'" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n='.$strHTMLControlName['VALUE'].'&k='.$code.'\', 900, 700);">&nbsp;
                        <span>'.$elUrl.'</span>
                    </td>
                </tr>';

        return $result;
    }

    private static function showCss()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .cl {cursor: pointer;}
                .mf-gray {color: #797777;}
                .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px!important; margin-left: -300px!important; border-bottom: 1px #e0e8ea solid!important;}
                .mf-fields-list.active {display: block;}
                .mf-fields-list td {padding-bottom: 5px;}
                .mf-fields-list td:first-child {width: 300px; color: #616060;}
                .mf-fields-list td:last-child {padding-left: 5px;}
                .mf-fields-list input[type="text"] {width: 350px!important;}
                .mf-fields-list textarea {min-width: 350px; max-width: 650px; color: #000;}
                .mf-fields-list img {max-height: 150px; margin: 5px 0;}
                .mf-img-table {background-color: #e0e8e9; color: #616060; width: 100%;}
                .mf-fields-list input[type="text"].adm-input-calendar {width: 170px!important;}
                .mf-file-name {word-break: break-word; padding: 5px 5px 0 0; color: #101010;}
                .mf-fields-list input[type="text"].mf-inp-bind-elem {width: unset!important;}
            </style>
            <?
        }
    }

    private static function showJs()
    {
        $showText = Loc::getMessage('CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('CPROP_HIDE_TEXT');

        CJSCore::Init(array("jquery"));
        if(!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                $(document).on('click', 'a.mf-toggle', function (e) {
                    e.preventDefault();

                    var table = $(this).closest('tr').find('table.mf-fields-list');
                    $(table).toggleClass('active');
                    if($(table).hasClass('active')){
                        $(this).text('<?=$hideText?>');
                    }
                    else{
                        $(this).text('<?=$showText?>');
                    }
                });

                $(document).on('click', 'a.mf-delete', function (e) {
                    e.preventDefault();

                    var textInputs = $(this).closest('tr').find('input[type="text"]');
                    $(textInputs).each(function (i, item) {
                        $(item).val('');
                    });

                    var textarea = $(this).closest('tr').find('textarea');
                    $(textarea).each(function (i, item) {
                        $(item).text('');
                    });

                    var checkBoxInputs = $(this).closest('tr').find('input[type="checkbox"]');
                    $(checkBoxInputs).each(function (i, item) {
                        $(item).attr('checked', 'checked');
                    });

                    $(this).closest('tr').hide('slow');
                });
            </script>
            <?
        }
    }

    private static function showJsForSetting($inputName)
    {
        CJSCore::Init(array("jquery"));
        ?>
        <script>
            function addNewRows() {
                $("#many-fields-table").append('' +
                    '<tr valign="top">' +
                    '<td><input type="text" class="inp-code" size="20"></td>' +
                    '<td><input type="text" class="inp-title" size="35"></td>' +
                    '<td><input type="text" class="inp-sort" size="5" value="500"></td>' +
                    '<td><select class="inp-type"><?=self::getOptionList()?></select></td>' +
                    '</tr>');
            }


            $(document).on('change', '.inp-code', function(){
                var code = $(this).val();

                if(code.length <= 0){
                    $(this).closest('tr').find('input.inp-title').removeAttr('name');
                    $(this).closest('tr').find('input.inp-sort').removeAttr('name');
                    $(this).closest('tr').find('select.inp-type').removeAttr('name');
                }
                else{
                    $(this).closest('tr').find('input.inp-title').attr('name', '<?=$inputName?>[' + code + '_TITLE]');
                    $(this).closest('tr').find('input.inp-sort').attr('name', '<?=$inputName?>[' + code + '_SORT]');
                    $(this).closest('tr').find('select.inp-type').attr('name', '<?=$inputName?>[' + code + '_TYPE]');
                }
            });

            $(document).on('input', '.inp-sort', function(){
                var num = $(this).val();
                $(this).val(num.replace(/[^0-9]/gim,''));
            });
        </script>
        <?
    }

    private static function showCssForSetting()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .many-fields-table {margin: 0 auto; /*display: inline;*/}
                .mf-setting-title td {text-align: center!important; border-bottom: unset!important;}
                .many-fields-table td {text-align: center;}
                .many-fields-table > input, .many-fields-table > select{width: 90%!important;}
                .inp-sort{text-align: center;}
                .inp-type{min-width: 125px;}
            </style>
            <?
        }
    }

    private static function prepareSetting($arSetting)
    {
        $arResult = [];

        foreach ($arSetting as $key => $value){
            if(strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            }
            else if(strstr($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arResult[$code]['SORT'] = $value;
            }
            else if(strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }

        if(!function_exists('cmp')){
            function cmp($a, $b)
            {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }
                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        }

        uasort($arResult, 'cmp');

        return $arResult;
    }

    private static function getOptionList($selected = 'string')
    {
        $result = '';
        $arOption = [
            'string' => Loc::getMessage('CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('CPROP_FIELD_TYPE_TEXT'),
            'date' => Loc::getMessage('CPROP_FIELD_TYPE_DATE'),
            'element' => Loc::getMessage('CPROP_FIELD_TYPE_ELEMENT'),
            'html' => Loc::getMessage('CPROP_FIELD_TYPE_HTML')
        ];

        foreach ($arOption as $code => $name){
            $s = '';
            if($code === $selected){
                $s = 'selected';
            }

            $result .= '<option value="'.$code.'" '.$s.'>'.$name.'</option>';
        }

        return $result;
    }

    
    private static function getExtension($filePath)
    {
        return array_pop(explode('.', $filePath));
    }
}
    