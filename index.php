<?php

error_reporting(E_ALL);

ini_set('display_errors', true);

require "bootstrap.php";

$config = require "app/config/main.php";

$connection = new \app\db\Connection($config['database']);

$edgesGateway = new \app\tableGateways\EdgesGateway($connection);
$nodesGateway = new \app\tableGateways\NodesGateway($connection);

$nodesGateway->delete(null);
$edgesGateway->delete(null);

$nodesList = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
$links = [
    ['A', 'D'],
    ['B', 'D'],
    ['B', 'E'],
    ['C', 'E'],
    ['D', 'F'],
    ['D', 'G'],
    ['E', 'G'],
    ['E', 'H'],
    ['G', 'I'],
    ['G', 'J'],
    ['G', 'K']
];

$graph = new \app\Graph($nodesGateway, $edgesGateway);

foreach ($nodesList as $nodeName) {
    $graph->addNode($nodeName);
}

foreach ($links as $link) {
    $graph->addLink($nodesGateway->getNodeIdByName($link[0]), $nodesGateway->getNodeIdByName($link[1]));
}

$childsNodes = $graph->getChilds($nodesGateway->getNodeIdByName('D'));
$parentNodes = $graph->getParents($nodesGateway->getNodeIdByName('D'));
var_dump($childsNodes);
var_dump($parentNodes);
