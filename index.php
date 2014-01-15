<?php
/**
 * Add our data to Neo4j index.
 * 
 * @link https://github.com/agentile/neo4j-social-graph-example
 * @version 0.1.0
 * @author Anthony Gentile <asgentile@gmail.com>
 */    
require 'vendor/autoload.php';
require 'vendor/cargomedia/socialgraph.php';

$time_start = microtime(true);

$sg = new CargoMedia\SocialGraph('data.json');
$sg->indexData();

$elapsed_time = microtime(true) - $time_start;

echo "Time to index: $elapsed_time seconds" . PHP_EOL;
