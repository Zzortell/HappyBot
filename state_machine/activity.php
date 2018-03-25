<?php

class Category
{
    public $name;
    public $activities;
    public $keyword;

    public function __construct(string $name, string $keyword, Array $activities = []) {
        $this->name = $name;
        $this->keyword = $keyword;
        $this->activities = $activities;
    }

    public function count() {
        $sum = 0;
        foreach ($this->activities as $activity) {
            $sum += $activity->count();
        }
        return $sum;
    }
}

class Activity
{
    public $name;
    public $owner;
    public $participants = [];
    public $max;
    public $channel;

    public function __construct(string $name, string $owner, Array $participants, int $max) {
        $this->name = $name;
        $this->owner = $owner;
        $this->participants = $participants;
        $this->max = $max;


    }

    public function count() {
        return 1 + count($this->participants);
    }
}

function in($substr, $str) {
    return strpos(strtolower($str), strtolower($substr)) !== false;
}

$userData = []; // id => [ 'state' => $state, 'category' => $category ]
$categories = [
    new Category('*Eat* together', 'eat', [
        new Activity('Italian restaurant', 'D. Dupont', [], 3),
        new Activity('Fairouz', 'A. Labaki', [], 5),
        new Activity('Sushi Palace', 'A. Lebeau', [], 10),
    ]),
    new Category('Play *games*', 'game', [
    ]),
    new Category('*Sports*', 'sport', [
    ]),
    new Category('Have a *drink*', 'drink', [
    ]),
    new Category('Discover the *city*', 'city', [
    ]),
];

/**
 * @return string $message
 */
function answer($message, $user) {
    global $userData;

    if( !array_key_exists($user, $userData) ){
        $userData[$user]['state'] = "hello";
    }

    $state = $userData[$user]['state'];
    [$response, $new_state] = $state($message, $user);
    $userData[$user]['state'] = $new_state;
    return $response;
}

function redirect($message, $followingState, $messageToPass = '', $user) {
    [$response, $new_state] = $followingState($messageToPass, $user);
    return [
        $message . "\n" . $response,
        $new_state
    ];
}

function hello($message, $user) {
    return redirect("Hey, I’m HappyBot, may I help you?\n", 'start', '', $user);
}

function start($message, $user) {
    return [
        "Are you looking for an *activity* or *support*?\n",
        'start_waiting'
    ];
}

function start_waiting($message, $user){
    global $categories;
    if (in('activity', $message)) {
        $response = '';
        foreach ($categories as $category) {
            $response .=  "\n - " . $category->name;
        }
        return [
            "These are the current most popular activities in the company:\n" . $response,
            'category'
        ];
    } else if (in('support', $message)) {
        return [
            "Can you specify more about the help you need\n - Mentoring\n - Administration\n - Directions\n",
            'support'
        ];
    } else {
        return redirect("sorry, I couldn't understand your request", 'start', '', $user);
    }
}

function category($message, $user) {
    global $categories;
    global $userData;
    foreach ($categories as $category) {
        if (in($category->keyword, $message)) {
            $userData[$user]['category'] = $category;

            $response = '';
            foreach ($category->activities as $activity) {
                if ($activity->count() < $activity->max) {
                    $response .= sprintf(
                        "\n - %s proposed to go to %s (%d people joined, %d place" .
                        ($activity->max - $activity->count() > 1 ? "s" : '') . " left.)",
                        $activity->name,
                        $activity->owner,
                        $activity->count(),
                        $activity->max - $activity->count()
                    );
                }
            }
            return [
                $category->count() . " persons want to " . strtolower($category->name) . "today:\n" . $response .
                "\nPlease choose one from the list",
                'known_activity'
            ];
        }
    }

    $userData[$user]['category'] = $message;
    return [
        "Cette activité n'existe pas, voulez-vous en créer une nouvelle ? [Oui, Non]",
        'unknown_activity'
    ];
}

function known_activity($message, $user) {
    global $userData;
    $category = $userData[$user]['category'];

    foreach ($category->activities as $activity) {
        if ($activity->count() < $activity->max && in($activity->name, $message)) {
            $activity->participants[] = $user;

            return redirect("You have joined " . $activity->name . " proposed by " . $activity->owner . "!", 'start', '', $user);
        }
    }
    return redirect("sorry, you're request dont correspond to any choice in the list, try again\n", 'category', $category->key, $user);

}

function unknown_activity($message, $user) {
    if (strpos(strtolower($message), 'o') === 0) {
        return [
            "Nom de l'activité",
            'create_activity_with_name'
        ];
    } else {
        return start_waiting('activity', $user);
    }
}

function create_activity_with_name($message, $user) {
    global $userData;
    $userData[$user]['activity_name'] = $message;
    return [
        "Combien de participants au maximum :",
        'create_activity'
    ];
}

function create_activity($message, $user) {
    global $userData;
    $activity = new Activity;
    $activity->name = $userData[$user]['activity_name'];
    $activity->owner = $user;
    $activity->max = (int) $message;
    $categories[$userData[$user]['category']] = $activity;
    return redirect("Activity created successfully.\n",start,'',$user);
}

require_once('support.php');
