<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
?>


<div class="row">
    <div class="col-md-6 col-xs-12">
        <form id="geoip-search-form">
            <div class="form-group">
                <label for="exampleInputEmail1">IP address</label>
                <input type="text" class="form-control" id="ip-address" name="ip" placeholder="Введите IP-адрес"
                       required>
            </div>
            <button type="submit" class="btn btn-success">Запросить</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-xs-12">
        <div class="list-group" id="result">

        </div>
    </div>
</div>

<script>
    var params = <?=\Bitrix\Main\Web\Json::encode(['signedParameters' => $this->getComponent()->getSignedParameters()])?>;
</script>

