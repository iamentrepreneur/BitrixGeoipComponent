BX.ready(function() {
    BX.bind(BX('geoip-search-form'), 'submit', function(event) {
        event.preventDefault();

        var ipAddress = BX('ip-address').value;

        // Обращаемся к ajax action компонента
        BX.ajax.runComponentAction('geoip_search', 'executeAjax', {
            mode: 'class',
            data: {
                signedParameters:params.signedParameters,
                sessid: BX.bitrix_sessid(),
                ip: ipAddress
            }
        }).then(function(response) {
            // Обработка успешного ответа
            if (response.data && response.data.UF_IP) {
                var resultDiv = BX('result');
                resultDiv.innerHTML = `
                    <span class="list-group-item active">Результат</span>
                    <span class="list-group-item"><b>IP:</b> ${response.data.UF_IP}</span>
                    <span class="list-group-item"><b>Страна:</b> ${response.data.UF_COUNTRY}</span>
                    <span class="list-group-item"><b>Город:</b> ${response.data.UF_CITY}</span>
                `;
            } else {
                BX('result').innerHTML = '<div class="alert alert-warning" role="alert">Данные не найдены.</div>';
            }
        }).catch(function(error) {
            // Обработка неуспешного ответа
            BX('result').innerHTML = '<div class="alert alert-danger" role="alert">Произошла ошибка при запросе данных.</div>';
        });
    });
});

