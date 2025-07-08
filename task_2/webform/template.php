<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title">Связаться</div>
        <div class="contact-form__head-text">Наши сотрудники помогут выполнить подбор услуги и расчет цены с учетом ваших требований</div>
    </div>

    <?if ($arResult["isFormErrors"] == "Y"):?>
        <div class="form-error"><?=$arResult["FORM_ERRORS_TEXT"]?></div>
    <?endif;?>

    <?=$arResult["FORM_HEADER"]?>
        <div class="contact-form__form-inputs">
            <?foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
                <?if ($FIELD_SID == "MESSAGE"): continue; endif;?>
                <div class="input contact-form__input">
                    <label class="input__label" for="<?=$arQuestion["STRUCTURE"][0]["ID"]?>">
                        <div class="input__label-text"><?=$arQuestion["CAPTION"]?><?=($arQuestion["REQUIRED"] == "Y" ? "*" : "")?></div>
                        <?=$arQuestion["HTML_CODE"]?>
                        <div class="input__notification"><?=($arQuestion["REQUIRED"] == "Y" ? "Обязательное поле" : "")?></div>
                    </label>
                </div>
            <?endforeach;?>
        </div>

        <div class="contact-form__form-message">
            <div class="input">
                <label class="input__label">
                    <div class="input__label-text"><?=$arResult["QUESTIONS"]["MESSAGE"]["CAPTION"]?></div>
                    <?=$arResult["QUESTIONS"]["MESSAGE"]["HTML_CODE"]?>
                </label>
            </div>
        </div>

        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">
                Нажимая «Отправить», Вы подтверждаете согласие на обработку персональных данных.
            </div>
            <button class="form-button contact-form__bottom-button">
                <div class="form-button__title"><?=$arResult["arForm"]["BUTTON"]?></div>
            </button>
        </div>
    <?=$arResult["FORM_FOOTER"]?>
</div>
