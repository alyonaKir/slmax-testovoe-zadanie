<?php

namespace project;

use User\Testproject\Person;
use User\Testproject\PeopleList;

include 'vendor/autoload.php';
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
$person  = new Person(2, "John", "Smith", "2004-06-07", 0, "Minsk");
try {
    $people = new PeopleList(2, '<');
} catch (\Exception $e) {
    echo $e->getMessage();
}

