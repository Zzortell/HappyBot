<?php

class User{
    public $id;
    public $name;
    public $gender;
    public $year;
    public $department;

    public function __construct(string $id, string $name, string $gender, int $year, string $department){
        $this->id = $id;
        $this->name = $name;
        $this->gender = $gender;
        $this->year = $year;
        $this->department = $department;
    }
}

$users = [
    new User('', 'Jean', 'homme', 2, 'HR'),
    new User('', 'Daniel', 'homme', 5, 'Managment'),
    new User('', 'Sara', 'femme', 1, 'HR'),
    new User('', 'Rachel', 'femme', 10, 'Administration'),
];
$administrations = [''];
$directions = ['cafeteria' => ['The cafeteria','Main hall of both A and B batiment'], 'Jean' => ['Jean','First floor building A, office 123']];

function support($message,$user){
    global $users;
    global $administrations;
    global $directions;
    if ( in('mentor',$message) ) {
        $response ='';
        foreach($users as $users_user){

            $response .= "\n - " . ($users_user->gender === 'homme' ? "Mr" : "Md") . sprintf(" %s from the %s department, he has been in the company for %d years now",$users_user->name,$users_user->department,$users_user->year);
        }
        return ["\nchoose your mentor from the list:" . $response . "\n" , 'mentor'];
    } else if ( in('administration',$message) ) {
      $adminQuestions = "";
      foreach($administration as $key => $adm){
          $adminQuestions .= "\n - $adm[0]";
      }
        return ["what's your question?\n" . $adminQuestions . "\n",'admin'];
    } else if ( in('directions',$message) ) {
      $direction = "";
      foreach($directions as $key => $dir){
          $direction .= "\n - $dir[0]";
      }
        return ["where do you need to go?" . $direction . "\n" ,'direction'];
    } else {
        return redirect("sorry! I couldn't understand your request\n",'start_waiting','support',$user);
    }
}

function mentor($message,$user){
    global $users;
    foreach($users as $users_user){
        if ( in($users_user->name, $message) ) {
            return redirect(
                'great, ' . ($users_user->gender == 'homme' ? "Mr. " : "Md. ") . $users_user->name . " has been chosen as your mentor\n",
                'start', '', $user
            );
        }
    }
    return redirect("sorry, I couldn't find the mentor you're looking for\n", support, 'mentor', $user);
}

function direction($message,$user){
         global $directions;
         foreach($directions as $key => $dir){
             if( in($key,$message)){
           return redirect($dir[1] . "\n" , 'start' ,'',$user);
       }
         }

         return redirect("sorry, I couldn't find the place or person you're looking for\n", 'support', 'direction', $user);
}

function admin($message,$user){
    global $administrations;
    foreach($administrations as $key => $adm){
        if( in($key,$message)){
            return redirect($adm[1] . "\n" , 'start' ,'',$user);
        }
    }
    return redirect("sorry, I couldn't find anything about your question\n", 'support', 'direction', $user);
}
