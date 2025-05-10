<?php

function afficherMenu() {
    echo "\n===== Menu Principal =====\n";
    echo "1. Nouvelle partie\n";
    echo "2. Historique des parties\n";
    echo "3. Statistiques\n";
    echo "4. Quitter\n";
    echo "Votre choix : ";
}

function obtenirChoixJoueur() {
    echo "\nChoisissez entre pierre, feuille ou ciseau (ou tapez 'menu' pour revenir) : ";
    $choix = trim(fgets(STDIN));
    return strtolower($choix);
}

function choixCPU() {
    $options = ['pierre', 'feuille', 'ciseau'];
    return $options[array_rand($options)];
}

function determinerGagnant($joueur, $cpu) {
    if ($joueur === $cpu) return 'égalité';
    if (
        ($joueur === 'pierre' && $cpu === 'ciseau') ||
        ($joueur === 'feuille' && $cpu === 'pierre') ||
        ($joueur === 'ciseau' && $cpu === 'feuille')
    ) {
        return 'joueur';
    } else {
        return 'cpu';
    }
}

function sauvegarderPartie($joueur, $cpu, $resultat) {
    $partie = [
        'date' => date('Y-m-d H:i:s'),
        'joueur' => $joueur,
        'cpu' => $cpu,
        'resultat' => $resultat
    ];
    $fichier = 'historique.json';
    if (!file_exists($fichier)) {
        $donnees = [];
    } else {
        $contenu = file_get_contents($fichier);
        $donnees = json_decode($contenu, true);
    }
    $donnees[] = $partie;
    file_put_contents($fichier, json_encode($donnees));
}

function afficherHistorique() {
    echo "\n===== Historique des parties =====\n";
    if (!file_exists('historique.json')) {
        echo "Aucune partie jouée.\n";
        return;
    }
    $data = json_decode(file_get_contents('historique.json'), true);
    foreach ($data as $partie) {
        echo "{$partie['date']} | Joueur: {$partie['joueur']} | CPU: {$partie['cpu']} | Résultat: {$partie['resultat']}\n";
    }
}

function afficherStatistiques() {
    echo "\n===== Statistiques =====\n";
    if (!file_exists('historique.json')) {
        echo "Pas assez de données.\n";
        return;
    }
    $data = json_decode(file_get_contents('historique.json'), true);
    $jouees = count($data);
    $victoires = 0;
    $choix = [ 'pierre' => 0, 'feuille' => 0, 'ciseau' => 0 ];
    $victoiresParMain = [ 'pierre' => 0, 'feuille' => 0, 'ciseau' => 0 ];

    foreach ($data as $partie) {
        if ($partie['resultat'] === 'joueur') {
            $victoires++;
            $victoiresParMain[$partie['joueur']]++;
        }
        $choix[$partie['joueur']]++;
    }

    echo "Parties jouées : $jouees\n";
    echo "Victoires : $victoires\n";
    echo "Taux de victoire : " . round(($victoires / $jouees) * 100, 2) . "%\n";

    $maxMain = array_search(max($victoiresParMain), $victoiresParMain);
    echo "Main la plus gagnante : $maxMain\n";

    foreach ($victoiresParMain as $main => $nombre) {
        $taux = $choix[$main] > 0 ? round(($nombre / $choix[$main]) * 100, 1) : 0;
        echo "- $main : $taux% de victoire\n";
    }
}

function lancerNouvellePartie() {
    while (true) {
        $choixJoueur = obtenirChoixJoueur();
        if ($choixJoueur === 'menu') return;
        if (!in_array($choixJoueur, ['pierre', 'feuille', 'ciseau'])) {
            echo "Choix invalide.\n";
            continue;
        }

        $choixCpu = choixCPU();
        echo "Le CPU a choisi : $choixCpu\n";
        $resultat = determinerGagnant($choixJoueur, $choixCpu);

        if ($resultat === 'égalité') {
            echo "C'est une égalité !\n";
        } elseif ($resultat === 'joueur') {
            echo "Vous avez gagné !\n";
        } else {
            echo "Vous avez perdu...\n";
        }

        sauvegarderPartie($choixJoueur, $choixCpu, $resultat);

        echo "\n1. Rejouer\n2. Menu principal\nVotre choix : ";
        $suite = trim(fgets(STDIN));
        if ($suite != '1') return;
    }
}


while (true) {
    afficherMenu();
    $choix = trim(fgets(STDIN));

    switch ($choix) {
        case '1':
            lancerNouvellePartie();
            break;
        case '2':
            afficherHistorique();
            break;
        case '3':
            afficherStatistiques();
            break;
        case '4':
            echo "A bientôt !\n";
            exit;
        default:
            echo "Choix non reconnu.\n";
    }
}
