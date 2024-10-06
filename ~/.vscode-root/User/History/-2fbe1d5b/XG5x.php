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

    // Construct the command array
    $command = array(
        $venv_python,
        $python_script,
        escapeshellarg($bottom_left_lat),
        escapeshellarg($bottom_left_lon),
        escapeshellarg($upper_right_lat),
        escapeshellarg($upper_right_lon),
        escapeshellarg($plantation_type),
        escapeshellarg($desired_date)
    );

    // Execute the command and capture output
    $output = null;
    $return_var = null;
    exec(implode(' ', $command), $output, $return_var);

    if ($return_var !== 0) {
        echo "Error: Failed to execute the Python script. Return code: $return_var";
        exit();
    }

    // Display raw output for debugging
    echo "Raw output from Python script:<br>";
    echo "<pre>" . implode("\n", $output) . "</pre>";

    // Process each line of output
    $results = array();
    $warnings = array();
    $info_messages = array();
    foreach ($output as $line) {
        $result = json_decode($line, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($result['debug'])) {
                echo "Debug information:<br>";
                echo "<pre>" . print_r($result['debug'], true) . "</pre>";
            } elseif (isset($result['error'])) {
                echo "Error from Python script: " . $result['error'];
                if (isset($result['args'])) {
                    echo "<br>Arguments received by Python script:<br>";
                    echo "<pre>" . print_r($result['args'], true) . "</pre>";
                }
                exit();
            } elseif (isset($result['warning'])) {
                $warnings[] = $result['warning'];
            } elseif (isset($result['info'])) {
                $info_messages[] = $result['info'];
            } else {
                // Store successful results
                $results[] = $result;
            }
        } else {
            echo "Error: Failed to decode JSON from Python script. Line: " . htmlspecialchars($line);
            exit();
        }
    }

    // Display warnings and info messages
    if (!empty($warnings)) {
        echo "<h3>Warnings:</h3>";
        echo "<ul>";
        foreach ($warnings as $warning) {
            echo "<li>" . htmlspecialchars($warning) . "</li>";
        }
        echo "</ul>";
    }

    if (!empty($info_messages)) {
        echo "<h3>Information:</h3>";
        echo "<ul>";
        foreach ($info_messages as $info) {
            echo "<li>" . htmlspecialchars($info) . "</li>";
        }
        echo "</ul>";
    }

    // Process successful results
    if (!empty($results)) {
        // ... [Rest of the code for processing results and generating HTML remains the same] ...
    } else {
        echo "Error: No valid results returned from Python script.";
    }
} else {
    echo "Invalid request method";
}
?>