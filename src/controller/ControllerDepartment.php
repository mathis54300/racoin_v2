<?php

namespace src\controller;

use src\model\Departement;

class ControllerDepartment
{

    protected array $departments = array();

    public function getAllDepartments()
    {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }
}
