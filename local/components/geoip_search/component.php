<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('highloadblock');
Loc::loadMessages(__FILE__);

$component = new GeoIpSearchComponent();
$component->initComponent('geoip_search');
$component->executeComponent();