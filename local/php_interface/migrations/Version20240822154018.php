<?php

namespace Sprint\Migration;

class Version20240822154018 extends Version
{
    protected $author = "admin";
    protected $description = "Создаем HL блок GeoIP для хранения данных о местоположении по IP";
    protected $moduleVersion = "4.12.2";

    /**
     * @return bool|void
     * @throws Exceptions\HelperException
     */
    public function up()
    {
        $helper = $this->getHelperManager();

        // Создаем HL блок
        $hlblockId = $helper->Hlblock()->saveHlblock([
            'NAME' => 'GeoIP',
            'TABLE_NAME' => 'b_hl_geoip',
        ]);

        // Создаем поля для инфоблока
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_' . $hlblockId, 'UF_IP', [
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_IP',
            'SORT' => 100,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'S',
            'EDIT_FORM_LABEL' => ['ru' => 'IP-адрес', 'en' => 'IP Address'],
            'LIST_COLUMN_LABEL' => ['ru' => 'IP-адрес', 'en' => 'IP Address'],
            'LIST_FILTER_LABEL' => ['ru' => 'IP-адрес', 'en' => 'IP Address'],
        ]);

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_' . $hlblockId, 'UF_COUNTRY', [
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_COUNTRY',
            'SORT' => 200,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'S',
            'EDIT_FORM_LABEL' => ['ru' => 'Страна', 'en' => 'Country'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Страна', 'en' => 'Country'],
            'LIST_FILTER_LABEL' => ['ru' => 'Страна', 'en' => 'Country'],
        ]);

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('HLBLOCK_' . $hlblockId, 'UF_CITY', [
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_CITY',
            'SORT' => 300,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'S',
            'EDIT_FORM_LABEL' => ['ru' => 'Город', 'en' => 'City'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Город', 'en' => 'City'],
            'LIST_FILTER_LABEL' => ['ru' => 'Город', 'en' => 'City'],
        ]);
    }

    public function down()
    {
        $helper = $this->getHelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockIdIfExists('GeoIP');

        $helper->UserTypeEntity()->deleteUserTypeEntitiesIfExists(
            'HLBLOCK_' . $hlblockId,
            [
                'UF_IP',
                'UF_COUNTRY',
                'UF_CITY',
            ]
        );
        $helper->Hlblock()->deleteHlblock($hlblockId);
    }
}
