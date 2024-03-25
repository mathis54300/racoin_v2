<?php

namespace src\db;

use Illuminate\Database\Capsule\Manager as DB;

class connection
{

    public static function createConn(): void
    {
        $capsule = new DB;
        $capsule->addConnection(parse_ini_file(__DIR__ . '/../../config/config.ini'));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
