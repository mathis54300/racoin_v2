<?php

namespace src\middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BreadcrumbsMiddleware
{
    public function __construct(protected \Slim\Views\Twig $view)
    {
    }

    public function __invoke(RequestInterface $request, RequestHandlerInterface $handler)
    {
        $path = explode('/', $request->getUri()->getPath());
        if ($path[0] === $path[1] && $path[0] === '') {
            array_shift($path);
        }
        $breadCrumbs = [];
        foreach ($path as $key => $value) {
            $value = $value === '' ? 'Home' : $value;
            $href = implode('/', array_slice($path, 0, $key + 1));
            if ($href === '') {
                $href = '/';
            }
            $breadCrumbs[] = [
                'text' => $value,
                'href' => $href
            ];
        }

        $this->view->getEnvironment()->addGlobal('breadcrumb', $breadCrumbs);
        $this->view->getEnvironment()->addGlobal('chemin', '/');

        return $handler->handle($request);
    }
}
