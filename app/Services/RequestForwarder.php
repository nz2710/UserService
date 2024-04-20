<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RequestForwarder
{
    public function forwardRequest(Request $request, $method, $url)
    {
        $response = (new Client())->request($method, $url, [
            'headers' => $request->headers->all() + ['Accept' => 'application/json'],
            'body' => $request->getContent(),
            'query' => $method === 'GET' ? $request->query() : [],
        ]);

        $content = json_decode((string) $response->getBody(), true);
        return response()->json($content, $response->getStatusCode());
    }
}
