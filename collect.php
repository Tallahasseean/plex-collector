<?php

/**
 * This script fetches all TV shows from a specified library section in Plex and adds episodes that contain a specific keyword to a manual collection.
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

// Configurable variables
$plexHost = 'http://192.168.4.189'; // Replace with your Plex server IP
$plexPort = '32400';  // Replace with your Plex server port if necessary
$token = 'SANDBmL1iYPNk34jKQRM'; // Replace with your Plex API token
$libraryName = 'TV Shows';  // Specify the library name
$collectionName = "Thanksgiving";  // Name of the collection
$keyword = 'thanksgiving';  // Keyword to search for in titles and descriptions

// Plex API base URL
$client = new Client([
    'base_uri' => "$plexHost:$plexPort",
    'headers' => [
        'X-Plex-Token' => $token,
    ]
]);

try {
    // Step 1: Get the server identifier dynamically
    $serversResponse = $client->get('/servers');
    $servers = simplexml_load_string($serversResponse->getBody()->getContents());
    $serverId = (string)$servers->Server['machineIdentifier'];

    // Step 2: Get the library section ID dynamically based on the library name
    $sectionsResponse = $client->get('/library/sections');
    $sections = simplexml_load_string($sectionsResponse->getBody()->getContents());

    $tvShowsSectionId = null;
    foreach ($sections->Directory as $section) {
        if (strtolower((string)$section['title']) == strtolower($libraryName)) {
            $tvShowsSectionId = (string)$section['key'];
            break;
        }
    }

    if ($tvShowsSectionId === null) {
        throw new Exception("Could not find the library section '$libraryName'");
    }

    // Step 3: Get the collection ID dynamically
    $collectionsResponse = $client->get("/library/sections/$tvShowsSectionId/collections");
    $collections = simplexml_load_string($collectionsResponse->getBody()->getContents());

    $collectionId = null;
    foreach ($collections->Directory as $collection) {
        if (strtolower((string)$collection['title']) == strtolower($collectionName)) {
            $collectionId = (string)$collection['ratingKey'];
            break;
        }
    }

    if ($collectionId === null) {
        throw new Exception("Could not find the collection '$collectionName'");
    }

    // Step 4: Fetch all TV shows from the TV Shows section
    $showsResponse = $client->get("/library/sections/$tvShowsSectionId/all");
    $shows = simplexml_load_string($showsResponse->getBody()->getContents());

    $count = 0;

    // Step 5: Loop through all shows
    foreach ($shows->Directory as $show) {
        $showTitle = (string)$show['title'];
        $showKey = (string)$show['key']; // e.g., '/library/metadata/1499'

        // Fetch all seasons for the show
        $seasonsResponse = $client->get($showKey);
        $seasons = simplexml_load_string($seasonsResponse->getBody()->getContents());

        // Loop through each season of the show
        foreach ($seasons->Directory as $season) {
            $seasonTitle = (string)$season['title'];
            $seasonKey = (string)$season['key']; // e.g., '/library/metadata/1500/children'

            // Fetch all episodes for the season
            $episodesResponse = $client->get($seasonKey);
            $episodes = simplexml_load_string($episodesResponse->getBody()->getContents());

            // Loop through each episode of the season
            foreach ($episodes->Video as $episode) {
                $count++;
                $summary = strtolower((string)$episode['summary']);
                $episodeTitle = (string)$episode['title'];

                // Check if the episode description contains the keyword
                if (stripos($summary, $keyword) !== false || stripos($episodeTitle, $keyword) !== false) {
                    echo "Found episode: " . $episodeTitle . " (Season " . $seasonTitle . ")\n";

                    // Add to the manual collection
                    $ratingKey = (string)$episode['ratingKey'];

                    // Add the episode to the manual collection via the metadata endpoint
                    $addCollectionResponse = $client->put("/library/collections/$collectionId/items", [
                        'query' => [
                            'uri' => "server://$serverId/com.plexapp.plugins.library/library/metadata/$ratingKey",
                        ]
                    ]);

                    if ($addCollectionResponse->getStatusCode() == 200) {
                        echo "Added to manual collection: $collectionName (Key: $ratingKey)\n";
                    } else {
                        echo "Failed to add episode: $episodeTitle to collection.\n";
                    }
                }
            }
        }
    }

    echo "Total episodes checked: $count\n";

} catch (ClientException $e) {
    // Catch the exception and get the response
    if ($e->hasResponse()) {
        $response = $e->getResponse();
        $responseBody = $response->getBody()->getContents();
        echo "Error: " . $responseBody;  // Output the detailed error response
    } else {
        echo 'Unknown error occurred.';
    }
}
catch (Exception $e) {
    echo $e->getTraceAsString();
    echo 'Error: ' . $e->getMessage();
}
