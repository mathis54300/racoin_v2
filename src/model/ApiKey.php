<?php

namespace src\model;

class ApiKey extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'apikey';
    protected $primaryKey = 'id_key';
    public $timestamps = false;

}
?>