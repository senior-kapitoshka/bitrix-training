<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$APPLICATION->SetAdditionalCSS($templateFolder . "/style.css");
?>

<div id="barba-wrapper">
  <div class="article-list">
    <?php foreach ($arResult["ITEMS"] as $item): ?>
      <a class="article-item article-list__item" href="<?= $item["DETAIL_PAGE_URL"] ?>" data-anim="anim-3">
        <div class="article-item__background">
          <?php if (is_array($item["PREVIEW_PICTURE"])): ?>
            <img
              src="<?= $item["PREVIEW_PICTURE"]["SRC"] ?>"
              alt="<?= htmlspecialchars($item["NAME"]) ?>"
            />
          <?php endif; ?>
        </div>
        <div class="article-item__wrapper">
          <div class="article-item__title"><?= htmlspecialchars($item["NAME"]) ?></div>
          <div class="article-item__content"><?= $item["PREVIEW_TEXT"] ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>