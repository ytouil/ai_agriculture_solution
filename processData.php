<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $bottom_left_lat = $_POST['bottom_left_lat'];
    $bottom_left_lon = $_POST['bottom_left_lon'];
    $upper_right_lat = $_POST['upper_right_lat'];
    $upper_right_lon = $_POST['upper_right_lon'];
    $plantation_type = $_POST['plantation_type'];
    $desired_date = isset($_POST['desired_date']) ? $_POST['desired_date'] : ''; // Assurez-vous que ce champ est dans votre formulaire

    // Chemin vers le script Python
    $python_script = "C:\\xampp\\htdocs\\websiteNasa\\src\\calculate_indices.py";
    
    // Commande pour exécuter le script Python
    $command = "python \"$python_script\" \"$bottom_left_lat\" \"$bottom_left_lon\" \"$upper_right_lat\" \"$upper_right_lon\" \"$plantation_type\" \"$desired_date\" 2>&1"; // Capturer les erreurs

    // Exécutez la commande et capturez la sortie
    $output = shell_exec($command);
    
    // Décodez la sortie JSON
    $results = json_decode($output, true);

    // Vérifiez si le décodage JSON a réussi
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur lors du décodage de la réponse JSON : " . json_last_error_msg();
        exit();
    }

    // Vérifiez que les données existent dans le tableau de résultats
    if (!isset($results['gdd_data'])) {
        echo "Données manquantes dans la réponse du script Python.";
        exit();
    }

    // Génération du fichier HTML
    $html_content = file_get_contents('C:\\xampp\\htdocs\\websiteNasa\\resultNASA_template.html');
    
    // Remplacement des espaces réservés par les données réelles
    $html_content = str_replace('{{PLANTATION_TYPE}}', $plantation_type, $html_content);
    $html_content = str_replace('{{DESIRED_DATE}}', $desired_date, $html_content);
    
    $gdd_rows = '';
    foreach ($results['gdd_data'] as $data) {
        $gdd_rows .= "<tr><td>{$data['date']}</td><td>{$data['daily_gdd']}</td><td>{$data['cumulative_gdd']}</td></tr>";
    }
    $html_content = str_replace('{{GDD_ROWS}}', $gdd_rows, $html_content);
    
    $html_content = str_replace('{{GROWTH_POTENTIAL}}', $results['growth_potential'], $html_content);

    // Écrire le contenu HTML généré dans resultNASA.html
    file_put_contents('C:\\xampp\\htdocs\\websiteNasa\\resultNASA.html', $html_content);

    // Redirection vers la page de résultats
    header("Location: resultNASA.html");
    exit();
} else {
    echo "Méthode de requête invalide.";
}
?>
