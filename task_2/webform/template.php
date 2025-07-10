<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>


<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?=$arResult['FORM_TITLE']?></div>
        <?php if ($arResult["isFormDescription"]): ?>
            <div class="contact-form__head-text"><?=$arResult['FORM_DESCRIPTION']?></div>
        <?php endif; ?>
    </div>

    <?php if ($arResult["isFormErrors"] == "Y"): ?>
        <div class="form-error"><?=$arResult["FORM_ERRORS_TEXT"]?></div>
    <?php endif; ?>

    <?php if ($arResult["isFormNote"] == "Y"): ?>
        <div class="form-success"><?=$arResult["FORM_NOTE"]?></div>
    <?php else: ?>

    <form
        class="contact-form__form"
        method="POST"
        action="<?=$arResult["FORM_ACTION"]?>"
    >
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="WEB_FORM_ID" value="<?=$arResult["arForm"]["ID"]?>">
        <input type="hidden" name="web_form_submit" value="Y">

        <div class="contact-form__form-inputs">
            <?php 
            if (isset($arResult['arAnswers']['name'])):
                $input_name = "form_text_" . $arResult['arAnswers']['name'][0]['ID'];
            ?>
                <div class="input contact-form__input">
                    <label class="input__label" for="medicine_name">
                        <div class="input__label-text">Ваше имя*</div>
                        <input class="input__input" type="text" id="medicine_name" name="<?=$input_name?>" required>
                        <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                    </label>
                </div>
            <?php endif; ?>

            <?php 
            if (isset($arResult['arAnswers']['company'])):
                $input_name = "form_text_" . $arResult['arAnswers']['company'][0]['ID'];
            ?>
                <div class="input contact-form__input">
                    <label class="input__label" for="medicine_company">
                        <div class="input__label-text">Компания/Должность*</div>
                        <input class="input__input" type="text" id="medicine_company" name="<?=$input_name?>" required>
                        <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                    </label>
                </div>
            <?php endif; ?>

            <?php 
            if (isset($arResult['arAnswers']['email'])):
                $input_name = "form_email_" . $arResult['arAnswers']['email'][0]['ID'];
            ?>
                <div class="input contact-form__input">
                    <label class="input__label" for="medicine_email">
                        <div class="input__label-text">Email*</div>
                        <input class="input__input" type="email" id="medicine_email" name="<?=$input_name?>" required>
                        <div class="input__notification">Неверный формат почты</div>
                    </label>
                </div>
            <?php endif; ?>

            <?php 
            if (isset($arResult['arAnswers']['phone'])):
                $input_name = "form_text_" . $arResult['arAnswers']['phone'][0]['ID'];
            ?>
                <div class="input contact-form__input">
                    <label class="input__label" for="medicine_phone">
                        <div class="input__label-text">Номер телефона*</div>
                        <input class="input__input" type="tel" id="medicine_phone"
                            data-inputmask="'mask': '+79999999999','clearIncomplete': 'true'"
                            maxlength="12" name="<?=$input_name?>" required>
                    </label>
                </div>
            <?php endif; ?>
        </div>

        <?php 
        if (isset($arResult['arAnswers']['message']) && is_array($arResult['arAnswers']['message'])):
            $input_name = "form_text_" . $arResult['arAnswers']['message'][0]['ID'];
        ?>
            <div class="contact-form__form-message">
                <div class="input">
                    <label class="input__label" for="medicine_message">
                        <div class="input__label-text">Сообщение</div>
                        <textarea class="input__input" id="medicine_message" name="<?=$input_name?>" required><?=htmlspecialchars($_POST[$input_name] ?? '')?></textarea>
                    </label>
                </div>
            </div>
        <?php endif; ?>


        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">
                Нажимая &laquo;Отправить&raquo;, Вы подтверждаете, что ознакомлены и принимаете условия &laquo;Согласия на обработку персональных данных&raquo;.
            </div>
            <button type="submit" name="web_form_send" class="form-button contact-form__bottom-button" 
            data-success="Отправлено"
            data-error="Ошибка отправки">
                <div class="form-button__title">Оставить заявку</div>
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>
