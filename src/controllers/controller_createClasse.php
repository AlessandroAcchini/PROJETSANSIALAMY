<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Http\Request;
use App\Http\Response;
use App\Config\Database;
use App\Config\Niveaux;

function creerClasse(Request $request, Response $response): void
{
    $data = [
        'error' => null,
        'success' => null,
        'nomClasse' => '',
        'niveauClasse' => '',
        'niveaux' => Niveaux::all(),

    ];

    if ($request->isPost()) {
        $nomClasse = trim((string) $request->post('nomClasse', ''));
        $niveauClasse = trim((string) $request->post('niveauClasse', ''));
        $data['nomClasse']    = $nomClasse;
        $data['niveauClasse'] = $niveauClasse;
        
        if ($nomClasse === '') {
            $data['error'] = 'Le nom de la classe est obligatoire !';
        }elseif($niveauClasse === ''){
            $data['error'] = 'Le niveau de la classe est obligatoire !';
        }

        // S’il y a une erreur de validation, on NE VA PAS en BDD
        if ($data['error'] === null) {
            try {
                $pdo = Database::getConnection();

                // Vérifier si la classe existe déjà
                $stmt = $pdo->prepare('SELECT id FROM classes WHERE nomClasse = :nomClasse');
                $stmt->execute([':nomClasse' => $nomClasse]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $data['error'] = 'Une classe avec ce nom existe déjà.';
                } else {

                    $stmt = $pdo->prepare(
                        'INSERT INTO classes (nomClasse, niveauClasse)
                         VALUES (:nomClasse, :niveauClasse)'
                    );
                    $stmt->execute([
                        ':nomClasse'=> $nomClasse,
                        ':niveauClasse' => $niveauClasse,
                    ]);

                    // Redirection
                    $response->redirect('index.php?action=chemin_pour_voir_les_classes');
                    return;
                }
            } catch (PDOException $e) {
                $data['error'] = 'Erreur base de données : ' . $e->getMessage();
            }
        }
    }
    $response->view(__DIR__ . '/../../templates/pages/createClasse.php', $data);
}