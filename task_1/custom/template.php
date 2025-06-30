<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?$APPLICATION->SetAdditionalCSS($templateFolder."/style.css");?>

<div class="news-list-wrapper">
    <?if($arParams["DISPLAY_TOP_PAGER"]):?>
        <div class="news-pagination top"><?=$arResult["NAV_STRING"]?></div>
    <?endif;?>

    <div class="news-list">
        <?foreach($arResult["ITEMS"] as $arItem):?>
            <?
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            ?>
            <div class="news-card" id="<?=$this->GetEditAreaId($arItem['ID']);?>">

                <div class="news-card__content">
                    <?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
                        <div class="news-card__date"><?=$arItem["DISPLAY_ACTIVE_FROM"]?></div>
                    <?endif;?>

                    <div class="news-card__title">
                        <?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
                            <a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
                        <?endif;?>
                    </div>

                    <?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
                        <div class="news-card__preview"><?=$arItem["PREVIEW_TEXT"]?></div>
                    <?endif;?>
                </div>
            </div>
        <?endforeach;?>
    </div>

    <?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
        <div class="news-pagination bottom"><?=$arResult["NAV_STRING"]?></div>
    <?endif;?>
</div>
