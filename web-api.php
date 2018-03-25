<?php

function createChannel(string $token) {
    $url = 'https://slack.com/api/channels.create)';
    echo $url;

    $client = new GuzzleHttp\Client();

    $request = new \GuzzleHttp\Psr7\Request('POST', $url, [
        'Authorization' => $token,
    ], json_encode([
        // 'name' =>
    ]));
    $promise = $client->sendAsync($request)->then(function ($response) {
        echo 'I completed! ' . $response->getBody();
        // then redirect user to this channel
    });
    // $promise->wait();
}
