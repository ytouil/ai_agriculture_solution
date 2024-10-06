<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bottom_left_lat = $_POST['bottom_left_lat'];
    $bottom_left_lon = $_POST['bottom_left_lon'];
    $upper_right_lat = $_POST['upper_right_lat'];
    $upper_right_lon = $_POST['upper_right_lon'];
    $plantation_type = $_POST['plantation_type'];
    $desired_date = $_POST['desired_date'];

    // Check if all required fields are set
    if (empty($bottom_left_lat) || empty($bottom_left_lon) || empty($upper_right_lat) || 
        empty($upper_right_lon) || empty($plantation_type) || empty($desired_date)) {
        echo "Error: All fields are required.";
        exit();
    }

    $venv_python = "/opt/lampp/htdocs/websiteNasa/websiteNasa_new_venv/bin/python";
    $python_script = "/opt/lampp/htdocs/websiteNasa/calculateIndices.py";

    $command = escapeshellcmd("$venv_python $python_script '$bottom_left_lat' '$bottom_left_lon' '$upper_right_lat' '$upper_right_lon' '$plantation_type' '$desired_date' 2>&1");

    $output = shell_exec($command);

    if ($output === null) {
        echo "Error: Failed to execute the Python script.";
        exit();
    }

    // Display raw output for debugging
    echo "Raw output from Python script:<br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";

    $results = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error: Failed to decode JSON from Python script. Error: " . json_last_error_msg();
        exit();
    }

    if (isset($results['debug'])) {
        echo "Debug information:<br>";
        echo "<pre>" . print_r($results['debug'], true) . "</pre>";
    }

    if (isset($results['error'])) {
        echo "Error from Python script: " . $results['error'];
        if (isset($results['args'])) {
            echo "<br>Arguments received by Python script:<br>";
            echo "<pre>" . print_r($results['args'], true) . "</pre>";
        }
        exit();
    }

    // ... rest of your PHP code ...
}
?>