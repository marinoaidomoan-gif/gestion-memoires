<?php

session_start();

// Autoload des classes core
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

$router = new Router();

// -------------------------------------------------------
// Auth
// -------------------------------------------------------
$router->add('login',    'AuthController', 'login');
$router->add('register', 'AuthController', 'register');
$router->add('logout',   'AuthController', 'logout');

// -------------------------------------------------------
// Mémoires
// -------------------------------------------------------
$router->add('memoires',          'MemoireController', 'index');
$router->add('memoire/detail',    'MemoireController', 'detail');
$router->add('memoire/soumettre', 'MemoireController', 'soumettre');
$router->add('memoire/modifier',  'MemoireController', 'modifier');
$router->add('memoire/evaluer',   'MemoireController', 'evaluer');
$router->add('memoire/valider',   'MemoireController', 'valider');
$router->add('memoire/rejeter',   'MemoireController', 'rejeter');
$router->add('memoire/assigner',  'MemoireController', 'assigner');
$router->add('memoire/upload',    'MemoireController', 'upload');

// -------------------------------------------------------
// Commentaires & Likes
// -------------------------------------------------------
$router->add('commentaire/ajouter',   'CommentaireController', 'ajouter');
$router->add('commentaire/modifier',  'CommentaireController', 'modifier');
$router->add('commentaire/supprimer', 'CommentaireController', 'supprimer');
$router->add('like/toggle',           'LikeController',        'toggle');

// -------------------------------------------------------
// Dashboards
// -------------------------------------------------------
$router->add('etudiant/dashboard',   'EtudiantController',  'dashboard');
$router->add('professeur/dashboard', 'ProfesseurController', 'dashboard');
$router->add('admin/dashboard',      'AdminController',      'dashboard');

// -------------------------------------------------------
// Etudiant — pages supplémentaires
// -------------------------------------------------------
$router->add('etudiant/memoire', 'EtudiantController', 'voirMemoire');
$router->add('etudiant/profil',  'EtudiantController', 'profil');

// -------------------------------------------------------
// Professeur — pages supplémentaires
// -------------------------------------------------------
$router->add('professeur/memoire',  'ProfesseurController', 'voirMemoire');
$router->add('professeur/evaluer',  'ProfesseurController', 'evaluer');
$router->add('professeur/profil',   'ProfesseurController', 'profil');

// -------------------------------------------------------
// Admin — gestion utilisateurs
// -------------------------------------------------------
$router->add('admin/creer-compte',         'AdminController', 'creerCompte');
$router->add('admin/modifier-user',        'AdminController', 'modifierUser');
$router->add('admin/supprimer-user',       'AdminController', 'supprimerUser');
$router->add('admin/supprimer-commentaire','AdminController', 'supprimerCommentaire');
$router->add('admin/profil',               'AdminController', 'profil');

// -------------------------------------------------------
// Accueil → redirige vers liste mémoires
// -------------------------------------------------------
$router->add('accueil', 'MemoireController', 'index');

$router->dispatch();