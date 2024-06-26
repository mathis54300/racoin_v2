<?php
require 'vendor/autoload.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use src\controller\ControllerCategorie;
use src\controller\ControllerDepartment;
use src\controller\ControllerItem;
use src\db\connection;
use src\model\Annonce;
use src\model\Annonceur;
use src\model\Categorie;
use src\model\Departement;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

connection::createConn();

$app = require_once __DIR__ . '/config/bootstrap.php';

// Initialisation de Twig
$loader = new FilesystemLoader(__DIR__ . '/template');
$twig = new Environment($loader);


if (!isset($_SESSION)) {
    session_start();
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $token = md5(uniqid(rand(), TRUE));
    $_SESSION['token'] = $token;
    $_SESSION['token_time'] = time();
} else {
    $token = $_SESSION['token'];
}

$menu = [
    [
        'href' => './index.php',
        'text' => 'Accueil'
    ]
];

$chemin = dirname($_SERVER['SCRIPT_NAME']);

$cat = new ControllerCategorie();
$dpt = new ControllerDepartment();


$app->get('/add', function () use ($twig, $app, $menu, $chemin, $cat, $dpt) {
    $ajout = new ControllerItem();
    $ajout->addItemView($twig, $menu, $chemin, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->post('/add', function (Request $request) use ($twig, $app, $menu, $chemin) {
    $allPostVars = $request->getParsedBody();
    $ajout = new ControllerItem();
    $ajout->addNewItem($twig, $menu, $chemin, $allPostVars);
});

$app->get('/item/{id}/edit', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin) {
    $id = $arg['id'];
    $item = new ControllerItem();
    $item->modifyGet($twig, $menu, $chemin, $id);
});
$app->post('/item/{id}/edit', function (Request $request, Response $response, array $arg) use ($twig, $app, $menu, $chemin, $cat, $dpt) {
    $id = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item = new ControllerItem();
    $item->modifyPost($twig, $menu, $chemin, $id, $allPostVars, $cat->getCategories(), $dpt->getAllDepartments());
});

$app->map(['GET, POST'], '/item/{id}/confirm', function (Request $request, Response $response, array $arg) use ($twig, $app, $menu, $chemin) {
    $id = $arg['id'];
    $allPostVars = $request->getParsedBody();
    $item = new ControllerItem();
    $item->edit($twig, $menu, $chemin, $id, $allPostVars);
});

$app->get('/search', function () use ($twig, $menu, $chemin, $cat) {
    $s = new \src\controller\ControllerSearch();
    $s->show($twig, $menu, $chemin, $cat->getCategories());
});


$app->post('/search', function (Request $request) use ($app, $twig, $menu, $chemin, $cat) {
    $array = $request->getParsedBody();
    $s = new \src\controller\ControllerSearch();
    $s->research($array, $twig, $menu, $chemin, $cat->getCategories());

});

$app->get('/annonceur/{n}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $n = $arg['n'];
    $annonceur = new \src\controller\ControllerAnnonceur();
    $annonceur->afficherAnnonceur($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/del/{n}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin) {
    $n = $arg['n'];
    $item = new \src\controller\ControllerItem();
    $item->supprimerItemGet($twig, $menu, $chemin, $n);
});

$app->post('/del/{n}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $n = $arg['n'];
    $item = new \src\controller\ControllerItem();
    $item->supprimerItemPost($twig, $menu, $chemin, $n, $cat->getCategories());
});

$app->get('/cat/{n}', function (Request $request, Response $response, array $arg) use ($twig, $menu, $chemin, $cat) {
    $n = $arg['n'];
    $categorie = new \src\controller\ControllerCategorie();
    $categorie->displayCategorie($twig, $menu, $chemin, $cat->getCategories(), $n);
});

$app->get('/api[/]', function () use ($twig, $menu, $chemin, $cat) {
    $template = $twig->load('api.html.twig');
    $menu = array(
        array(
            'href' => $chemin,
            'text' => 'Acceuil'
        ),
        array(
            'href' => $chemin . '/api',
            'text' => 'Api'
        )
    );
    echo $template->render(array('breadcrumb' => $menu, 'chemin' => $chemin));
});

$app->group('/api', function () use ($app, $twig, $menu, $chemin, $cat) {


    $app->get('/annonce/{id}', function ($request, $response, $arg) use ($app) {
        $id = $arg['id'];
        $annonceList = ['id_annonce', 'id_categorie as categorie', 'id_annonceur as annonceur', 'id_departement as departement', 'prix', 'date', 'titre', 'description', 'ville'];
        $return = Annonce::select($annonceList)->find($id);

        if (isset($return)) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $return->categorie = Categorie::find($return->categorie);
            $return->annonceur = Annonceur::select('email', 'nom_annonceur', 'telephone')
                ->find($return->annonceur);
            $return->departement = Departement::select('id_departement', 'nom_departement')->find($return->departement);
            $links = [];
            $links['self']['href'] = '/api/annonce/' . $return->id_annonce;
            $return->links = $links;
            echo $return->toJson();
        } else {
            $app->notFound();
        }
        return $response;
    });

    $app->get('/annonces[/]', function (Request $request, Response $response) use ($app) {
        $annonceList = ['id_annonce', 'prix', 'titre', 'ville'];
        $response = $response->withHeader('Content-Type', 'application/json');
        $a = Annonce::all($annonceList);
        $links = [];
        foreach ($a as $ann) {
            $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
            $ann->links = $links;
        }
        $links['self']['href'] = '/api/annonces/';
        $a->links = $links;
        echo $a->toJson();
        return $response;
    });
    $app->get('/categorie/{id}', function (Request $request, Response $response, array $arg) use ($app) {
        $id = $arg['id'];
        $response = $response->withHeader('Content-Type', 'application/json');
        $a = Annonce::select('id_annonce', 'prix', 'titre', 'ville')
            ->where('id_categorie', '=', $id)
            ->get();
        $links = [];

        foreach ($a as $ann) {
            $links['self']['href'] = '/api/annonce/' . $ann->id_annonce;
            $ann->links = $links;
        }

        $c = Categorie::find($id);
        $links['self']['href'] = '/api/categorie/' . $id;
        $c->links = $links;
        $c->annonces = $a;
        echo $c->toJson();
        return $response;
    });

    $app->get('/categories[/]', function (Request $request, Response $response, array $arg) use ($app) {
        $response = $response->withHeader('Content-Type', 'application/json');
        $c = Categorie::get();
        $links = [];
        foreach ($c as $cat) {
            $links['self']['href'] = '/api/categorie/' . $cat->id_categorie;
            $cat->links = $links;
        }
        $links['self']['href'] = '/api/categories/';
        $c->links = $links;
        echo $c->toJson();
        return $response;
    });

    $app->get('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $kg = new \src\controller\ControllerKey();
        $kg->show($twig, $menu, $chemin, $cat->getCategories());
    });

    $app->post('/key', function () use ($app, $twig, $menu, $chemin, $cat) {
        $nom = $_POST['nom'];

        $kg = new \src\controller\ControllerKey();
        $kg->generateKey($twig, $menu, $chemin, $cat->getCategories(), $nom);
    });
});

$app->run();
