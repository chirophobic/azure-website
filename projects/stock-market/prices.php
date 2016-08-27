<?php
/**
 * Simple script to generate some stock market data. The data will persist for 5 minutes then regenerate.
 *
 * The companies.txt file contains a list of companies that stock prices will be generated for.
 *
 * The price data is saved into the prices.dat file. This is a simple serialize. The structure of this data
 * is:
 *  [
 *      "timestamp": "<timestamp>",
 *      "prices": [
 *          "company id": <price>
 *      ]
 *  ]
 */

// The number of seconds that the prices should stay for before being required to regenerate
define("PRICE_LIFE", 10);
define("TIME_NOW", time());

define("PRICE_MIN", 10);
define("PRICE_MAX", 100000);

$existingPrices = null;

// Attempt to load the existing data
if (file_exists("prices.dat")) {
    $rawData = file_get_contents("prices.dat");

    if ($rawData) {
        $existingPrices = unserialize($rawData);

        if (TIME_NOW - $existingPrices["timestamp"] > PRICE_LIFE) {
            $existingPrices = null;
        }
    }
}

// If the existing data was not loaded. Either it didn't exist or was out of date
// we must regenerate it
if (!$existingPrices) {
    $existingPrices = [
        "timestamp" => TIME_NOW,
        "prices" => []
    ];

    $handle = @fopen("companies.txt", "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            $buffer = trim($buffer);
            $existingPrices["prices"][$buffer] = rand(PRICE_MIN, PRICE_MAX);
        }
        if (!feof($handle)) {
            die("Error: unexpected fgets() fail\n");
        }
        fclose($handle);
        file_put_contents("prices.dat", serialize($existingPrices));
    }
}

// Output the prices
$output = [];
foreach ($existingPrices["prices"] as $id => $price) {
    $output[] = "$id $price";
}
echo implode("\n", $output);