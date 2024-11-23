<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Geo IP component");
?>

<?$APPLICATION->IncludeComponent(
	"geoip_search", 
	".default", 
	array(
		"API_TOKEN" => "618c6805580d8ddddea7961c76e49e91",
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>