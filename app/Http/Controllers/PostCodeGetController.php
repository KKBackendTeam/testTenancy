<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class PostCodeGetController extends Controller
{
    /**
     * Fetch address information from an API based on the provided postcode.
     *
     * @param string $postcode The postcode to fetch address information for.
     * @return mixed The response body containing address information or error status.
     */
    public function postCodeFetchFromAPI($postcode)
    {
        $client = new Client();
        try {
            $response = $client->get("https://api.getaddress.io/find/" . $postcode . "?api-key=FwnyDJmi90-hnhOfsPClag18208&expand=true");
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                return response()->json(['status' => 400]);
            }
        }

        return $response->getBody();
    }
}
