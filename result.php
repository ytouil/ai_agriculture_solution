<?php
session_start();
$results = isset($_SESSION['calculation_results']) ? json_decode($_SESSION['calculation_results'], true) : null;

// Debugging: Log the raw results
error_log("Raw results: " . print_r($_SESSION['calculation_results'], true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculation Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Calculation Results</h1>
    <?php if ($results): ?>
        <?php if (isset($results['gdd_data'])): ?>
            <h2>GDD Data</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Daily GDD</th>
                        <th>Cumulative GDD</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($results['gdd_data'] as $data): ?>
                    <tr>
                        <td><?= htmlspecialchars($data['date']) ?></td>
                        <td><?= number_format($data['daily_gdd'], 2) ?></td>
                        <td><?= number_format($data['cumulative_gdd'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="error">No GDD data available.</p>
        <?php endif; ?>

        <?php if (isset($results['growth_potential'])): ?>
            <h2>Growth Potential</h2>
            <p>Growth Potential: <?= number_format($results['growth_potential'], 2) ?></p>
        <?php else: ?>
            <p class="error">No growth potential data available.</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="error">No results available. Please submit the form first.</p>
    <?php endif; ?>

    <a href="frontNASA.html" class="back-link">Back to form</a>

    <?php if ($results): ?>
        <h2>Debug Information</h2>
        <pre><?php print_r($results); ?></pre>
    <?php endif; ?>
</body>
</html>