<?php

require_once('vendor/autoload.php');
require_once('state_machine/activity.php');
require_once('web-api.php');

$loop = \React\EventLoop\Factory::create();

$client = new \Slack\RealTimeClient($loop);
$client->setToken('xoxb-335531298436-LrpvnWWhzQFJdbZZ62aEuuke');
$client->connect();

// Test
// send('channels.create');

$client->on('message', function ($data) use ($client) {
    if ($data['user'] !== 'U9VFM8SCU') {
        $client->getChannelGroupOrDMByID($data['channel'])->then(function ($channel) use ($client, $data) {
            $message = $client->getMessageBuilder()
                    ->setText(answer($data['text'], 'remi'))
                    ->setChannel($channel)
                    ->create();
            $client->postMessage($message);
        });
    }
});

$loop->run();
