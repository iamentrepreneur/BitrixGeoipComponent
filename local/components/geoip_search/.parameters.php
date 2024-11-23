<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    "PARAMETERS" => [
        "CACHE_TIME" => [
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Время кэширования",
            "TYPE" => "STRING",
            "DEFAULT" => "3600",
        ],
        "API_TOKEN" => [
            "PARENT" => "BASE",
            "NAME" => "API Токен",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "NOTIFY_EMAIL" => [
            "PARENT" => "BASE",
            "NAME" => "Email - для оповещение об ошибках",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
    ],
    "GROUPS" => [
        // Группы параметров (если есть)
    ],
);
