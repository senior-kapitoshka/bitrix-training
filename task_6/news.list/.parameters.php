<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_TYPE" => [
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "IBLOCK_ID" => [,
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "NEWS_COUNT" => [
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
        "CACHE_TIME" => [
            "DEFAULT" => 3600,
        ],
    ],
];
