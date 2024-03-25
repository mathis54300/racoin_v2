<?php

namespace src\controller;

use src\model\Departement;

class controllerDepartment {

    protected $departments = array();

    public function getAllDepartments() {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }
}