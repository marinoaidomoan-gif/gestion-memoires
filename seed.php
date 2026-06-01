<?php

// ============================================================
// SEED — Données de test
// Exécuter UNE SEULE FOIS : http://localhost/projet_memoire/seed.php
// Supprimer ce fichier après utilisation !
// ============================================================

require_once __DIR__ . '/core/Database.php';

$pdo = Database::getInstance()->getPdo();

echo "<pre style='font-family:monospace;padding:1rem'>";

try {
    // --------------------------------------------------------
    // 1. USERS
    // --------------------------------------------------------
    $users = [
        // [name, email, password_clair, role_info]
        ['Directeur Moussa',  'directeur@uatm.com',   'admin123',   'directeur'],
        ['Prof. Koné Bakary', 'prof.kone@uatm.com',   'prof123',    'professeur'],
        ['Prof. Diallo Sara', 'prof.diallo@uatm.com',  'prof123',    'professeur'],
        ['Etudiant Jean',     'jean@etudiant.com',     'etudiant123','etudiant'],
        ['Etudiant Marie',    'marie@etudiant.com',    'etudiant123','etudiant'],
        ['Consulteur Ahmed',  'ahmed@consul.com',      'consul123',  'consulteur'],
    ];

    $insertUser = $pdo->prepare("
        INSERT INTO users (name, email, password, date_inscription)
        VALUES (?, ?, ?, CURRENT_DATE)
    ");

    $ids = [];
    foreach ($users as [$name, $email, $password, $role]) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $insertUser->execute([$name, $email, $hash]);
        $ids[$email] = $pdo->lastInsertId();
        echo "✅ User créé : $name ($email) — mot de passe : $password\n";
    }

    // --------------------------------------------------------
    // 2. SOUS-TYPES
    // --------------------------------------------------------

    // Directeur
    $pdo->prepare("INSERT INTO directeur_etudes (idUser, bureau) VALUES (?, ?)")
        ->execute([$ids['directeur@uatm.com'], 'Bureau 01 — Bâtiment A']);

    // Professeurs
    $pdo->prepare("INSERT INTO professeur (idUser, specialite, grade, departement) VALUES (?, ?, ?, ?)")
        ->execute([$ids['prof.kone@uatm.com'], 'Intelligence Artificielle', 'Maître de conférences', 'Informatique']);

    $pdo->prepare("INSERT INTO professeur (idUser, specialite, grade, departement) VALUES (?, ?, ?, ?)")
        ->execute([$ids['prof.diallo@uatm.com'], 'Réseaux & Systèmes', 'Assistant', 'Informatique']);

    // Étudiants diplômés
    $pdo->prepare("INSERT INTO etudiant_diplome (idUser, niveau, filiere, annee_diplome) VALUES (?, ?, ?, ?)")
        ->execute([$ids['jean@etudiant.com'], 'L3', 'Génie Logiciel', 2025]);

    $pdo->prepare("INSERT INTO etudiant_diplome (idUser, niveau, filiere, annee_diplome) VALUES (?, ?, ?, ?)")
        ->execute([$ids['marie@etudiant.com'], 'M2', 'Réseaux & Télécoms', 2025]);

    // Étudiant consulteur
    $pdo->prepare("INSERT INTO etudiant_consulteur (idUser, niveau, filiere) VALUES (?, ?, ?)")
        ->execute([$ids['ahmed@consul.com'], 'L3', 'Génie Logiciel']);

    echo "\n✅ Sous-types créés\n\n";

    // --------------------------------------------------------
    // 3. MÉMOIRES
    // --------------------------------------------------------
    $insertMemoire = $pdo->prepare("
        INSERT INTO memoire
            (titre, theme, nbPages, centre, date_soumission, annee_academique,
             statut, fichier, idEtudiant, idProfesseur, idDirecteur)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $memoireData = [
        [
            'Détection de maladies agricoles par IA',
            'Intelligence Artificielle & Agriculture',
            87, 'Centre de Recherche UATM',
            '2025-03-15', 2025, 'valide', null,
            $ids['jean@etudiant.com'],
            $ids['prof.kone@uatm.com'],
            $ids['directeur@uatm.com'],
        ],
        [
            'Système de surveillance réseau en temps réel',
            'Cybersécurité & Réseaux',
            102, 'Département Informatique',
            '2025-04-20', 2025, 'en_attente', null,
            $ids['marie@etudiant.com'],
            $ids['prof.diallo@uatm.com'],
            null,
        ],
        [
            'Application mobile de gestion des notes étudiantes',
            'Développement Mobile',
            74, 'Département Informatique',
            '2025-05-01', 2025, 'en_attente', null,
            $ids['jean@etudiant.com'],
            $ids['prof.kone@uatm.com'],
            null,
        ],
    ];

    $memoireIds = [];
    foreach ($memoireData as $m) {
        $insertMemoire->execute($m);
        $memoireIds[] = $pdo->lastInsertId();
        echo "✅ Mémoire créé : {$m[0]} [{$m[6]}]\n";
    }

    // --------------------------------------------------------
    // 4. COMMENTAIRES
    // --------------------------------------------------------
    $insertComment = $pdo->prepare("
        INSERT INTO commentaire (contenu, date_comment, estModifie, idUser, idMemoire)
        VALUES (?, NOW(), 0, ?, ?)
    ");

    $comments = [
        ['Très bon travail sur la partie théorique !',     $ids['prof.kone@uatm.com'],   $memoireIds[0]],
        ['La méthodologie pourrait être approfondie.',      $ids['directeur@uatm.com'],   $memoireIds[0]],
        ['Intéressant comme sujet, bravo !',               $ids['ahmed@consul.com'],      $memoireIds[0]],
        ['Les références bibliographiques sont complètes.', $ids['prof.diallo@uatm.com'], $memoireIds[1]],
        ['Veuillez revoir la conclusion du chapitre 3.',   $ids['prof.diallo@uatm.com'], $memoireIds[2]],
    ];

    $commentIds = [];
    foreach ($comments as $c) {
        $insertComment->execute($c);
        $commentIds[] = $pdo->lastInsertId();
    }
    echo "\n✅ " . count($comments) . " commentaires créés\n";

    // --------------------------------------------------------
    // 5. LIKES
    // --------------------------------------------------------
    $insertLike = $pdo->prepare("
        INSERT INTO likes (dateLike, idUser, idMemoire, idCommentaire)
        VALUES (NOW(), ?, ?, ?)
    ");

    $likes = [
        // Likes sur mémoires
        [$ids['prof.kone@uatm.com'],   $memoireIds[0], null],
        [$ids['ahmed@consul.com'],      $memoireIds[0], null],
        [$ids['prof.diallo@uatm.com'], $memoireIds[1], null],
        // Likes sur commentaires
        [$ids['jean@etudiant.com'],    null, $commentIds[0]],
        [$ids['marie@etudiant.com'],   null, $commentIds[0]],
        [$ids['ahmed@consul.com'],     null, $commentIds[3]],
    ];

    foreach ($likes as $l) {
        $insertLike->execute($l);
    }
    echo "✅ " . count($likes) . " likes créés\n";

    // --------------------------------------------------------
    // RÉSUMÉ
    // --------------------------------------------------------
    echo "\n";
    echo "============================================\n";
    echo "  SEED TERMINÉ AVEC SUCCÈS ✅\n";
    echo "============================================\n\n";
    echo "COMPTES DE TEST :\n\n";
    echo "  Directeur  : directeur@uatm.com  / admin123\n";
    echo "  Professeur : prof.kone@uatm.com  / prof123\n";
    echo "  Professeur : prof.diallo@uatm.com / prof123\n";
    echo "  Étudiant   : jean@etudiant.com   / etudiant123\n";
    echo "  Étudiant   : marie@etudiant.com  / etudiant123\n";
    echo "  Consulteur : ahmed@consul.com    / consul123\n";
    echo "\n⚠️  SUPPRIME CE FICHIER MAINTENANT !\n";

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

echo "</pre>";