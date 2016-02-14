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

$nodesWithEdges = $graph->getNodesWithEdges();


$graphData = [
    'nodes' => [],
    'edges' => []
];

$colors = ['green', 'blue', 'red'];

foreach ($nodesWithEdges['nodes'] as $nodeData) {
    $graphData['nodes'][$nodeData['id']] = [
        'color' => $colors[array_rand($colors)],
        'shape' => 'dot',
        'label' => $nodeData['name']
    ];
}

foreach ($nodesWithEdges['edges'] as $edgeInfo) {
    $graphData['edges'][$edgeInfo['start']][$edgeInfo['end']] = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="web/assets/js/arbor.js"></script>
    <script src="web/assets/js/renderer.js"></script>
    <script src="web/assets/js/graphics.js"></script>
</head>
<body>
<canvas id="viewport" width="1024" height="768"></canvas>

<script src="web/assets/js/graph.js"></script>

<script type="text/javascript">
    var graphRenderer = new GraphRenderer("#viewport");
    graphRenderer.render(<?=json_encode($graphData)?>);
</script>
</body>
</html>
