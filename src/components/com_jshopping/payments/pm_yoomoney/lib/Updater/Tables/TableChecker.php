<?php

namespace YooMoney\Updater\Tables;

class TableChecker
{
    /**
     * Список таблиц, которые должны быть созданы для работы функций модуля
     */
    const TABLES = [
        'YoomoneyRefunds'
    ];

    public function checkTables()
    {
        foreach (self::TABLES as $table) {
            $this->{'check' . $table}();
        }
    }

    private function checkYoomoneyRefunds()
    {
        $db = \JFactory:: getDbo();
        $query = "CREATE TABLE IF NOT EXISTS `#__yoomoney_refunds` (
            `refund_id`        CHAR(36) NOT NULL,
            `order_id`          INTEGER  NOT NULL,
            `created_at`        DATETIME NOT NULL,
            CONSTRAINT `' . DB_PREFIX . 'yoomoney_refund_pk` PRIMARY KEY (`refund_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=UTF8 COLLATE=utf8_general_ci";

        $db->setQuery($query)->execute();
    }
}