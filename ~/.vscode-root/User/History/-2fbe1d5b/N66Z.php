<?php
// process_and_generate.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bottom_left_lat = $_POST['bottom_left_lat'];
    $bottom_left_lon = $_POST['bottom_left_lon'];
    $upper_right_lat = $_POST['upper_right_lat'];
    $upper_right_lon = $_POST['upper_right_lon'];
    $plantation_type = $_POST['plantation_type'];
    $desired_date = $_POST['desired_date'];

    // Simulated GDD data for Turf
    $gdd_data = [
        ["date" => "01/10/2024", "daily_gdd" => 12.5, "cumulative_gdd" => 12.5],
        ["date" => "02/10/2024", "daily_gdd" => 10.8, "cumulative_gdd" => 23.3],
        ["date" => "03/10/2024", "daily_gdd" => 11.0, "cumulative_gdd" => 34.3],
        ["date" => "04/10/2024", "daily_gdd" => 9.5, "cumulative_gdd" => 43.8],
        ["date" => "05/10/2024", "daily_gdd" => 10.2, "cumulative_gdd" => 54.0],
        ["date" => "06/10/2024", "daily_gdd" => 12.0, "cumulative_gdd" => 66.0]
    ];

    // Simulated growth potential and recommendations
    $growth_potential = "Based on the cumulative GDD (66.0), the turf is in the *active growth stage*. Here are the corresponding recommendations:";
    
    $recommendations = "
    1. *Mowing*: You should mow the turf regularly, maintaining a height of 3-4 cm. With active growth, you may need to mow every 4-7 days.
    2. *Irrigation*: Keep the turf well-watered, especially if the weather is warm and dry. Around *20-25 mm of water* per week is optimal.
    3. *Fertilization*: Apply *slow-release nitrogen fertilizer* to promote strong, healthy turf development. Around *1 kg of nitrogen per 100 m²*.
    4. *Pest and Disease Control*: Monitor the turf for pests and diseases like brown patch.
    5. *Aeration*: Consider *core aeration* to improve root growth and water infiltration.
    6. *Weed Management*: Use a selective herbicide for *broadleaf weed control* if necessary.
    ";

    // Load the result template
    $template = file_get_contents("./resultNASA_template.html");

    // Prepare GDD rows for the table
    $gdd_rows = '';
    foreach ($gdd_data as $row) {
        $gdd_rows .= "<tr><td>{$row['date']}</td><td>{$row['daily_gdd']}</td><td>{$row['cumulative_gdd']}</td></tr>";
    }

    // Replace placeholders in the template
    $template = str_replace('{{PLANTATION_TYPE}}', 'Turf', $template);
    $template = str_replace('{{DESIRED_DATE}}', $desired_date, $template);
    $template = str_replace('{{GDD_ROWS}}', $gdd_rows, $template);
    $template = str_replace('{{GROWTH_POTENTIAL}}', $growth_potential, $template);
    $template = str_replace('{{RECOMMENDATIONS}}', nl2br($recommendations), $template);

    // Output the result page
    echo $template;
}
?>
