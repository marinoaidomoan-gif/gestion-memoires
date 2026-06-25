<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../models/EtudiantDiplome.php';
require_once __DIR__ . '/../models/Professeur.php';
require_once __DIR__ . '/../models/DirecteurEtudes.php';

class MemoireController extends Controller {

    private Memoire $memoire;

    public function __construct() {
        $this->memoire = new Memoire();
    }

    // -------------------------------------------------------
    // GET /index.php?route=memoires
    // Liste publique des mémoires validés + recherche
    // -------------------------------------------------------
    public function index(): void {
        $motCle   = $this->get('q', '');
        $annee    = $this->get('annee', '');

        if (!empty($motCle)) {
            $memoires = $this->memoire->rechercher($motCle);
        } elseif (!empty($annee)) {
            $memoires = $this->memoire->findWhere([
                'statut'          => 'valide',
                'annee_academique' => $annee,
            ]);
        } else {
            $memoires = $this->memoire->findValides();
        }

        // Années disponibles pour le filtre
        $annees = $this->getAnneesDisponibles();

        $this->render('memoire/index', [
            'memoires' => $memoires,
            'annees'   => $annees,
            'motCle'   => $motCle,
            'annee'    => $annee,
        ]);
    }

    // -------------------------------------------------------
    // GET /index.php?route=memoire/detail&id=X
    // Détail d'un mémoire (public si validé)
    // -------------------------------------------------------
    public function detail(): void {
        $id = (int) $this->get('id', 0);

        if (!$id) {
            $this->redirect('/public/index.php?route=memoires');
            return;
        }

        $memoire = $this->memoire->findById($id);

        // Mémoire non trouvé ou pas encore validé → accès refusé sauf rôles internes
        if (!$memoire) {
            $this->redirect('/public/index.php?route=memoires');
            return;
        }

        if ($memoire['statut'] !== 'valide') {
            $rolesAutorises = ['professeur', 'directeur_etudes', 'etudiant_diplome'];
            if (!$this->estConnecte() || !in_array($_SESSION['role'], $rolesAutorises)) {
                $this->redirect('/public/index.php?route=memoires');
                return;
            }
            // L'étudiant diplômé ne voit que son propre mémoire non validé
            if ($_SESSION['role'] === 'etudiant_diplome'
                && $memoire['idEtudiant'] !== $_SESSION['idUser']) {
                $this->redirect('/public/index.php?route=memoires');
                return;
            }
        }

        $this->render('memoire/detail', ['memoire' => $memoire]);
    }

    // -------------------------------------------------------
    // GET  /index.php?route=memoire/soumettre
    // POST /index.php?route=memoire/soumettre
    // Réservé à : etudiant_diplome
    // -------------------------------------------------------
    public function soumettre(): void {
        $this->requiertRole('etudiant_diplome');

        $error   = null;
        $success = null;

        if ($this->isPost()) {
            $data = [
                'titre'            => $this->post('titre'),
                'theme'            => $this->post('theme'),
                'nbPages'          => $this->post('nbPages') ?: null,
                'centre'           => $this->post('centre') ?: null,
                'annee_academique' => $this->post('annee_academique'),
            ];

            $error = $this->validerSoumission($data);

            if (!$error && empty($_FILES['fichier']['name'])) {
                $error = "Le fichier du mémoire est obligatoire.";
            }

            if (!$error) {
                $etudiant = new EtudiantDiplome();
                $idMemoire = $etudiant->soumettreMemoire($data, $_FILES['fichier']);

                if ($idMemoire) {
                    $success = "Mémoire soumis avec succès. Il sera examiné prochainement.";
                } else {
                    $error = "Format de fichier non autorisé (PDF, DOC, DOCX uniquement).";
                }
            }
        }

        $this->render('memoire/soumettre', [
            'error'   => $error,
            'success' => $success,
        ]);
    }

    // -------------------------------------------------------
    // GET  /index.php?route=memoire/modifier&id=X
    // POST /index.php?route=memoire/modifier&id=X
    // Réservé à : etudiant_diplome (son propre mémoire en attente)
    // -------------------------------------------------------
    public function modifier(): void {
        $this->requiertRole('etudiant_diplome');

        $id = (int) $this->get('id', 0);
        if (!$id) {
            $this->redirect('/public/index.php?route=etudiant/dashboard');
            return;
        }

        $memoire = $this->memoire->findById($id);

        // Vérifier ownership + statut
        if (!$memoire
            || $memoire['idEtudiant'] !== $_SESSION['idUser']
            || $memoire['statut'] !== 'en_attente') {
            $this->redirect('/public/index.php?route=etudiant/dashboard');
            return;
        }

        $error   = null;
        $success = null;

        if ($this->isPost()) {
            $data = [
                'titre'            => $this->post('titre'),
                'theme'            => $this->post('theme'),
                'nbPages'          => $this->post('nbPages') ?: null,
                'centre'           => $this->post('centre') ?: null,
                'annee_academique' => $this->post('annee_academique'),
            ];

            $error = $this->validerSoumission($data);

            if (!$error) {
                $fichier  = !empty($_FILES['fichier']['name']) ? $_FILES['fichier'] : null;
                $etudiant = new EtudiantDiplome();

                if ($etudiant->modifierMemoire($id, $data, $fichier)) {
                    $success = "Mémoire modifié avec succès.";
                    $memoire = $this->memoire->findById($id); // Rafraîchir
                } else {
                    $error = "Impossible de modifier ce mémoire.";
                }
            }
        }

        $this->render('memoire/modifier', [
            'memoire' => $memoire,
            'error'   => $error,
            'success' => $success,
        ]);
    }

    // -------------------------------------------------------
    // POST /index.php?route=memoire/evaluer  (AJAX ou form)
    // Réservé à : professeur
    // -------------------------------------------------------
    public function evaluer(): void {
        $this->requiertRole('professeur');

        $id       = (int) $this->post('idMemoire');
        $decision = $this->post('decision');

        if (!$id || !in_array($decision, ['valide', 'rejete'])) {
            $this->json(['success' => false, 'message' => 'Données invalides.'], 400);
            return;
        }

        $professeur = new Professeur();
        $ok = $professeur->evaluerMemo($id, $decision);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Mémoire " . ($decision === 'valide' ? 'validé' : 'rejeté') . " avec succès."
                    : "Action impossible. Vérifiez que vous êtes bien l'encadrant de ce mémoire.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=professeur/dashboard');
        }
    }

    // -------------------------------------------------------
    // POST /index.php?route=memoire/valider  (directeur)
    // POST /index.php?route=memoire/rejeter  (directeur)
    // Réservé à : directeur_etudes
    // -------------------------------------------------------
    public function valider(): void {
        $this->requiertRole('directeur_etudes');
        $this->traiterDecisionDirecteur('valide');
    }

    public function rejeter(): void {
        $this->requiertRole('directeur_etudes');
        $this->traiterDecisionDirecteur('rejete');
    }

    // -------------------------------------------------------
    // POST /index.php?route=memoire/assigner
    // Assigner un professeur encadrant
    // Réservé à : directeur_etudes
    // -------------------------------------------------------
    public function assigner(): void {
        $this->requiertRole('directeur_etudes');

        $idMemoire    = (int) $this->post('idMemoire');
        $idProfesseur = (int) $this->post('idProfesseur');

        if (!$idMemoire || !$idProfesseur) {
            $this->json(['success' => false, 'message' => 'Données invalides.'], 400);
            return;
        }

        $directeur = new DirecteurEtudes();
        $ok = $directeur->assignerProfesseur($idMemoire, $idProfesseur);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Professeur assigné avec succès."
                    : "Impossible d'assigner ce professeur.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=admin/dashboard');
        }
    }

    // -------------------------------------------------------
    // GET /index.php?route=memoire/upload
    // POST /index.php?route=memoire/upload
    // Upload d'un ancien mémoire par le directeur
    // -------------------------------------------------------
    public function upload(): void {
        $this->requiertRole('directeur_etudes');

        $error   = null;
        $success = null;

        // Liste des étudiants diplômés pour le select
        $memoire   = new Memoire();
        $etudiants = $this->getEtudiantsDiplomes();

        if ($this->isPost()) {
            $data = [
                'titre'            => $this->post('titre'),
                'theme'            => $this->post('theme'),
                'nbPages'          => $this->post('nbPages') ?: null,
                'centre'           => $this->post('centre') ?: null,
                'date_soumission'  => $this->post('date_soumission'),
                'annee_academique' => $this->post('annee_academique'),
                'idEtudiant'       => (int) $this->post('idEtudiant'),
            ];

            if (empty($data['titre']) || empty($data['theme'])
                || empty($data['annee_academique']) || !$data['idEtudiant']) {
                $error = "Tous les champs obligatoires doivent être remplis.";
            } elseif (empty($_FILES['fichier']['name'])) {
                $error = "Le fichier est obligatoire.";
            } else {
                $directeur = new DirecteurEtudes();
                $idMemoire = $directeur->upload($data, $_FILES['fichier']);

                if ($idMemoire) {
                    $success = "Mémoire uploadé avec succès (ID #{$idMemoire}).";
                } else {
                    $error = "Format non autorisé ou erreur d'upload (PDF, DOC, DOCX).";
                }
            }
        }

        $this->render('memoire/upload', [
            'error'     => $error,
            'success'   => $success,
            'etudiants' => $etudiants,
        ]);
    }

    // -------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------

    private function traiterDecisionDirecteur(string $statut): void {
        $id = (int) $this->post('idMemoire');

        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID manquant.'], 400);
            return;
        }

        $directeur = new DirecteurEtudes();
        $ok = $statut === 'valide'
            ? $directeur->validerMemo($id)
            : $directeur->rejeterMemo($id);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Mémoire " . ($statut === 'valide' ? 'validé' : 'rejeté') . "."
                    : "Action impossible. Le mémoire n'est peut-être plus en attente.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=admin/dashboard');
        }
    }

    private function validerSoumission(array $data): ?string {
        if (empty($data['titre']))            return "Le titre est obligatoire.";
        if (empty($data['theme']))            return "Le thème est obligatoire.";
        if (empty($data['annee_academique'])) return "L'année académique est obligatoire.";
        if ($data['nbPages'] !== null && (!is_numeric($data['nbPages']) || $data['nbPages'] < 1)) {
            return "Le nombre de pages doit être un entier positif.";
        }
        return null;
    }

    private function getAnneesDisponibles() {
        return $this->memoire->getAnnees();
    }

    private function getEtudiantsDiplomes(): array {
        $stmt = $this->memoire->db->query(
            "SELECT u.idUser, u.name
             FROM users u
             JOIN etudiantdiplome ed ON u.idUser = ed.idUser
             ORDER BY u.name ASC"
        );
        return $stmt->fetchAll();
    }
}