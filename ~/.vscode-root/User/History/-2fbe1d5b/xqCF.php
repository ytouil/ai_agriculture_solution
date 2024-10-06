<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bottom_left_lat = $_POST['bottom_left_lat'];
    $bottom_left_lon = $_POST['bottom_left_lon'];
    $upper_right_lat = $_POST['upper_right_lat'];
    $upper_right_lon = $_POST['upper_right_lon'];
    $plantation_type = $_POST['plantation_type'];
    $desired_date = $_POST['desired_date'];

    $venv_python = "/opt/lampp/htdocs/websiteNasa/websiteNasa_new_venv/bin/python";
    $python_script = "/opt/lampp/htdocs/websiteNasa/calculateIndices.py";

    $command = "$venv_python $python_script '$bottom_left_lat' '$bottom_left_lon' '$upper_right_lat' '$upper_right_lon' '$plantation_type' '$desired_date'";

    $output = shell_exec($command);

    echo "Raw output from Python script:<br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";

    $lines = explode("\n", trim($output));
    $results = null;
    $errors = [];
    $warnings = [];
    $info = [];

    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($data['error'])) {
                $errors[] = $data['error'];
            } elseif (isset($data['warning'])) {
                $warnings[] = $data['warning'];
            } elseif (isset($data['info'])) {
                $info[] = $data['info'];
            } elseif (isset($data['gdd_data'])) {
                $results = $data;
            }
        }
    }

    if (!empty($errors)) {
        echo "Errors:<br>";
        foreach ($errors as $error) {
            echo "- " . htmlspecialchars($error) . "<br>";
        }
        exit();
    }

    if (!empty($warnings)) {
        echo "Warnings:<br>";
        foreach ($warnings as $warning) {
            echo "- " . htmlspecialchars($warning) . "<br>";
        }
    }

    if (!empty($info)) {
        echo "Info:<br>";
        foreach ($info as $i) {
            echo "- " . htmlspecialchars($i) . "<br>";
        }
    }

    if ($results === null) {
        echo "Error: No valid results returned from Python script.";
        exit();
    }

    $html_content = file_get_contents('resultNASA_template.html');
    
    if ($html_content === false) {
        echo "Error: Could not load the resultNASA_template.html file.";
        exit();
    }

    $html_content = str_replace('{{PLANTATION_TYPE}}', htmlspecialchars($plantation_type), $html_content);
    $html_content = str_replace('{{DESIRED_DATE}}', htmlspecialchars($desired_date), $html_content);
    
    $gdd_rows = '';
    foreach ($results['gdd_data'] as $data) {
        $gdd_rows .= "<tr><td>{$data['date']}</td><td>{$data['daily_gdd']}</td><td>{$data['cumulative_gdd']}</td></tr>";
    }
    $html_content = str_replace('{{GDD_ROWS}}', $gdd_rows, $html_content);

    $growth_potential = json_encode($results['growth_potential']);
    $html_content = str_replace('{{GROWTH_POTENTIAL}}', htmlspecialchars($growth_potential), $html_content);

    $result_file_path = '/opt/lampp/htdocs/websiteNasa/resultNASA.html';
    if (file_put_contents($result_file_path, $html_content) === false) {
        echo "Error: Failed to write resultNASA.html file.";
        exit();
    }

    echo "<a href='resultNASA.html'>View Results</a>";
} else {
    echo "Invalid request method";
}
?>