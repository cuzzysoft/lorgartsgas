<?php
header('Content-Type: application/json');

// Example data for the charts
$data = [
    "barChart" => [
        "labels" => ["January", "February", "March", "April", "May"],
        "income" => [1500, 1800, 2000, 1700, 1900],
        "expenditure" => [1200, 1400, 1600, 1300, 1500]
    ],
    "pieChart" => [
        "labels" => ["Gas Sales", "Gas Purchase", "Maintenance", "Equipment Purchase"],
        "values" => [5000, 3000, 800, 1200]
    ]
];

echo json_encode($data);
