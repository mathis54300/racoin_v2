<?php

namespace src\controller;

use Slim\Views\Twig;

abstract class DefaultController
{
    public function __construct(protected Twig $view)
    {
    }
}
