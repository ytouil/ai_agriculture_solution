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
            } else {
                // Store successful results
                $results[] = $result;
            }
        } else {
            echo "Error: Failed to decode JSON from Python script. Line: " . htmlspecialchars($line);
            exit();
        }
    }

    // Process successful results
    if (!empty($results)) {
        $html_content = file_get_contents('resultNASA_template.html');
        
        if ($html_content === false) {
            echo "Error: Could not load the resultNASA_template.html file.";
            exit();
        }

        $html_content = str_replace('{{PLANTATION_TYPE}}', htmlspecialchars($plantation_type), $html_content);
        $html_content = str_replace('{{DESIRED_DATE}}', htmlspecialchars($desired_date), $html_content);
        
        $gdd_rows = '';
        if (isset($results[0]['gdd_data']) && is_array($results[0]['gdd_data'])) {
            foreach ($results[0]['gdd_data'] as $data) {
                $gdd_rows .= "<tr><td>" . htmlspecialchars($data['date']) . "</td><td>" . htmlspecialchars($data['daily_gdd']) . "</td><td>" . htmlspecialchars($data['cumulative_gdd']) . "</td></tr>";
            }
        } else {
            echo "Error: Invalid GDD data returned from Python script.";
            exit();
        }
        $html_content = str_replace('{{GDD_ROWS}}', $gdd_rows, $html_content);

        if (isset($results[0]['growth_potential']) && is_array($results[0]['growth_potential'])) {
            $growth_potential = json_encode($results[0]['growth_potential']);
            $html_content = str_replace('{{GROWTH_POTENTIAL}}', htmlspecialchars($growth_potential), $html_content);
        } else {
            $html_content = str_replace('{{GROWTH_POTENTIAL}}', 'Unknown', $html_content);
        }

        $result_file_path = '/opt/lampp/htdocs/websiteNasa/resultNASA.html';
        if (file_put_contents($result_file_path, $html_content) === false) {
            echo "Error: Failed to write resultNASA.html file.";
            exit();
        }

        // Redirect to the resultNASA.html file
        header("Location: resultNASA.html");
        exit();
    } else {
        echo "Error: No valid results returned from Python script.";
    }
} else {
    echo "Invalid request method";
}
?>