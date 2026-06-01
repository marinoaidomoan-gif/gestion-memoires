<?php

// POINT D'ENTRÉE UNIQUE — Toutes les requêtes passent ici

session_start();

// --- Autoload des fichiers core et models ---
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Router.php';


// ROUTES
// Format : $router->add(METHOD, 'chemin', 'Controller', 'action')

$router = new Router();

// --- Authentification ---
$router->add('GET',  'auth/login',    'AuthController', 'loginForm');
$router->add('POST', 'auth/login',    'AuthController', 'login');
$router->add('GET',  'auth/logout',   'AuthController', 'logout');
$router->add('GET',  'auth/register', 'AuthController', 'registerForm');
$router->add('POST', 'auth/register', 'AuthController', 'register');

// --- Mémoires ---
$router->add('GET',  'memoire',               'MemoireController', 'index');
$router->add('GET',  'memoire/afficher/:id',  'MemoireController', 'afficher');
$router->add('GET',  'memoire/soumettre',     'MemoireController', 'soumettreForm');
$router->add('POST', 'memoire/soumettre',     'MemoireController', 'soumettre');
$router->add('POST', 'memoire/valider/:id',   'MemoireController', 'valider');
$router->add('POST', 'memoire/rejeter/:id',   'MemoireController', 'rejeter');

// --- Commentaires ---
$router->add('POST', 'commentaire/ajouter',        'CommentaireController', 'ajouter');
$router->add('POST', 'commentaire/modifier/:id',   'CommentaireController', 'modifier');
$router->add('POST', 'commentaire/supprimer/:id',  'CommentaireController', 'supprimer');

// --- Likes ---
$router->add('POST', 'like/toggle', 'LikeController', 'toggle');

// --- Admin (DirecteurEtudes) ---
$router->add('GET',  'admin/dashboard',         'AdminController', 'dashboard');
$router->add('GET',  'admin/users',             'AdminController', 'users');
$router->add('POST', 'admin/users/creer',       'AdminController', 'creerUser');
$router->add('POST', 'admin/users/supprimer/:id', 'AdminController', 'supprimerUser');

// --- Page d'accueil ---
$router->add('GET', '',         'MemoireController', 'index');
$router->add('GET', 'accueil',  'MemoireController', 'index');


// LANCEMENT

$router->dispatch();