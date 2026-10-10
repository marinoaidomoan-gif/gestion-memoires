<?php
if ($_SESSION['role'] === 'etudiant_diplome') {
    require_once __DIR__ . '/profil_diplome.php';
} else {
    require_once __DIR__ . '/profil_consulteur.php';
}