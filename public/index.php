<?php

session_start();

// -------------------------------------------------------
// Chargement du core
// -------------------------------------------------------
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Router.php';

// -------------------------------------------------------
// Définition des routes
// Format : route => [Controller, méthode]
// -------------------------------------------------------
$router = new Router();

// Auth
$router->add('login',      'AuthController', 'login');
$router->add('logout',     'AuthController', 'logout');

// Accueil (redirige selon le rôle)
$router->add('accueil',    'AuthController', 'accueil');

// Mémoires
$router->add('memoires',           'MemoireController', 'index');
$router->add('memoire.detail',     'MemoireController', 'detail');
$router->add('memoire.soumettre',  'MemoireController', 'soumettre');
$router->add('memoire.modifier',   'MemoireController', 'modifier');
$router->add('memoire.valider',    'MemoireController', 'valider');
$router->add('memoire.approuver',  'MemoireController', 'approuver');
$router->add('memoire.archiver',   'MemoireController', 'archiver');
$router->add('memoire.upload',     'MemoireController', 'upload');
$router->add('memoire.uploadExcel','MemoireController', 'uploadExcel');

// Commentaires
$router->add('commentaire.ajouter',   'CommentaireController', 'ajouter');
$router->add('commentaire.modifier',  'CommentaireController', 'modifier');
$router->add('commentaire.supprimer', 'CommentaireController', 'supprimer');
$router->add('commentaire.signaler',  'CommentaireController', 'signaler');

// Likes
$router->add('like.toggle', 'LikeController', 'toggle');

// Admin (DirecteurEtudes)
$router->add('admin.comptes',      'AdminController', 'gererComptes');
$router->add('admin.creerCompte',  'AdminController', 'creerCompte');
$router->add('admin.signalements', 'AdminController', 'gererSignalements');

// -------------------------------------------------------
// Lancer le routeur
// -------------------------------------------------------
$router->dispatch();