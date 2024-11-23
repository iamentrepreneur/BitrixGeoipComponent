<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Request;
use Bitrix\Main\Server;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;


/**
 * Для работы компонента по ajax, необходимо импементироваться от Controllerable
 * Так же есть ряд обязательных требований по реализации методов, подробнее в документации
 * https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=14014&LESSON_PATH=3913.3516.5062.3750.14014
 */

class GeoIpSearchComponent extends CBitrixComponent implements Controllerable
{
    protected ErrorCollection $errorCollection;

    public function configureActions(): array
    {
        return [];
    }

    // Необходимо для получения параметров компонента
    protected function listKeysSignedParameters(): array
    {
        return [
            'API_TOKEN',
            'NOTIFY_EMAIL',
        ];
    }

    // Пытаемся получить ip из базы
    /**
     * @throws LoaderException|SystemException
     */
    protected function getHLBlockData($ip): ?array
    {
        Loader::includeModule('highloadblock');

        $hlBlock = HL\HighloadBlockTable::getList([
            'filter' => ['NAME' => 'GeoIP']
        ])->fetch();

        if ($hlBlock) {
            $entity = HL\HighloadBlockTable::compileEntity($hlBlock);
            $dataClass = $entity->getDataClass();

            $result = $dataClass::getList([
                'filter' => ['UF_IP' => $ip]
            ])->fetch();

            return $result !== false ? $result : null;
        }

        return null;
    }

    // Фетчимся к сервер api.ipstack.com
    protected function fetchGeoIpData($ip): ?array
    {
        try {
            $apiUrl = "http://api.ipstack.com/$ip?access_key=" . urlencode($this->arParams['API_TOKEN']);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode != 200) {
                $errorMessage = "HTTP Error: $httpCode\n$response";
                $this->logAndNotifyError($errorMessage, $ip);
                return null;
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMessage = "JSON Error: " . json_last_error_msg() . "\n$response";
                $this->logAndNotifyError($errorMessage, $ip);
                return null;
            }

            // Проверка на ошибку в ответе API
            if (isset($data['success']) && $data['success'] === false) {
                $errorMessage = "API Error: " . ($data['error']['info'] ?? 'Unknown error');
                $this->logAndNotifyError($errorMessage, $ip);
                return null;
            }

            if (isset($data['country_name'], $data['city'])) {
                return [
                    'UF_IP' => $ip,
                    'UF_COUNTRY' => $data['country_name'] ?? '',
                    'UF_CITY' => $data['city'] ?? ''
                ];
            }

            return null;
        } catch (Exception $e) {
            $this->logAndNotifyError($e->getMessage(), $ip);
            return null;
        }
    }

    // Сохраняем в hl базу
    /**
     * @throws LoaderException|SystemException
     */
    protected function saveHLBlockData($data): void
    {
        Loader::includeModule('highloadblock');

        $hlBlock = HL\HighloadBlockTable::getList([
            'filter' => ['NAME' => 'GeoIP']
        ])->fetch();

        if ($hlBlock) {
            $entity = HL\HighloadBlockTable::compileEntity($hlBlock);
            $dataClass = $entity->getDataClass();

            $dataClass::add($data);
        }
    }

    // Выполняем компонент, отрисовываем шаблон
    public function executeComponent(): void
    {
        $this->includeComponentTemplate();
    }

    // Сам ajax action
    /**
     * @throws LoaderException|SystemException
     */
    public function executeAjaxAction(): ?array
    {
        $request = Context::getCurrent()->getRequest();

        $ip = htmlspecialchars($request['ip']);
        if ($ip) {
            $data = $this->getHLBlockData($ip);
            if (!$data) {
                $data = $this->fetchGeoIpData($ip);

                if ($data) {
                    $this->saveHLBlockData($data);
                }
            }
            return $data;
        }

        return null;
    }

    // Пушим ошибку на почту в случае появления проблем с фетчем
    protected function logAndNotifyError($errorMessage, $ip): void
    {
        // Запись ошибки в лог
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/error.log', $errorMessage . "\n", FILE_APPEND);

        // Отправка email уведомления
        Event::send(array(
            "EVENT_NAME" => "ERROR_NOTIFICATION",
            "LID" => "s1",
            "C_FIELDS" => array(
                "NOTIFY_EMAIL" => $this->arParams['NOTIFY_EMAIL'],
                "ERROR_MESSAGE" => $errorMessage,
                "IP_ADDRESS" => $ip,
            )
        ));
    }
}
