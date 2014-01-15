<?php
require 'vendor/autoload.php';
require 'vendor/cargomedia/socialgraph.php';

$sg = new CargoMedia\SocialGraph('data.json');
$sg->indexData();

foreach ($sg->getNodes() as $node) {
    // Direct friends
    echo $node->getProperty('firstName') . ' ' . $node->getProperty('surname') . ' is friends with: ' . PHP_EOL;
    $friends = $sg->fetchFriends($node->getProperty('id'));
    foreach ($friends as $f) {
        echo $f['firstName'] . ' ' . $f['surname'] . PHP_EOL;
    }
    echo '---------' . PHP_EOL;
    
    // Friends of friends
    echo "The friends of friends for " . $node->getProperty('firstName') . ' ' . $node->getProperty('surname') . ' are: ' . PHP_EOL;
    $fof = $sg->fetchFriendsOfFriends($node->getProperty('id'));
    foreach ($fof as $f) {
        echo $f['firstName'] . ' ' . $f['surname'] . PHP_EOL;
    }
    echo '---------' . PHP_EOL;
}


