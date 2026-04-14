<?php
require 'vendor/autoload.php';

use Algolia\AlgoliaSearch\Api\SearchClient;

$host = "localhost";
$user = "root";
$password = "";
$database = "movie"; 
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client = SearchClient::create(
    'DYMYCN7BKE', // Application ID
    'ee09b2154230972eb010fdd8709e49fc' // API Key
);

$sql = "SELECT * FROM moviedb"; // change table name if needed
$result = $conn->query($sql);

$objects = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['objectID'] = $row['id']; // required unique ID

        if (!empty($row['release_date'])) {
            $year = substr($row['release_date'], 0, 4);
            if (preg_match('/^\d{4}$/', $year)) {
                $row['year'] = $year;
            }
        }

        $objects[] = $row;
    }
}


if (!empty($objects)) {
    $requests = [];
    foreach ($objects as $object) {
        $requests[] = [
            'action' => 'addObject',
            'body' => $object,
        ];
    }

    $client->batch('movies', ['requests' => $requests]);
    $client->setSettings('movies', [
        'searchableAttributes' => ['title', 'overview', 'genre'],
        'attributesForFaceting' => ['searchable(genre)', 'year'],
        'customRanking' => ['desc(vote_average)'],
        'attributesToSnippet' => ['overview:20'],
        'snippetEllipsisText' => '…',
    ]);

    echo "Data successfully synced to Algolia!";
} else {
    echo "No records found.";
}

$conn->close();
?>