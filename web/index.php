<?php

error_reporting(E_ALL);

ini_set('display_errors', true);

require "../bootstrap.php";

$config = require "../app/config/main.php";

$connection = new \app\db\Connection($config['database']);

$edgesGateway = new \app\tableGateways\EdgesGateway($connection);
$nodesGateway = new \app\tableGateways\NodesGateway($connection);

$graph = new \app\Graph($nodesGateway, $edgesGateway);

$edgesGateway->delete(null);
$nodesGateway->delete(null);

$connection->execute('ALTER SEQUENCE nodes_id_seq RESTART WITH 1');

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



foreach ($nodesList as $nodeName) {
    $graph->addNode($nodeName);
}

foreach ($links as $link) {
    $graph->addLink($nodesGateway->getNodeIdByName($link[0]), $nodesGateway->getNodeIdByName($link[1]));
}

$graph->swapPositions(7, 9, 11);

$childsNodes = $graph->getChilds($nodesGateway->getNodeIdByName('D'));
$parentNodes = $graph->getParents($nodesGateway->getNodeIdByName('G'));


/**
 * Формирует массив для построения графа
 *
 * @param $nodes
 * @param $edges
 * @return array
 */
function getDataForGraph($nodes, $edges)
{
    $graphData = [
        'nodes' => [],
        'edges' => []
    ];

    $colors = ['green', 'blue', 'red'];

    foreach ($nodes as $nodeData) {
        $graphData['nodes'][$nodeData['id']] = [
            'color' => $colors[array_rand($colors)],
            'shape' => 'dot',
            'label' => $nodeData['name']
        ];
    }

    foreach ($edges as $edgeInfo) {
        $graphData['edges'][$edgeInfo['start']][$edgeInfo['end']] = [];
    }

    return $graphData;
}

$allNodes = $nodesGateway->getAll();
$childsNodes[] = $nodesGateway->getNodeByName('D');
$parentNodes[] = $nodesGateway->getNodeByName('G');

$allNodesGraph = getDataForGraph($allNodes, $edgesGateway->getByNodes(\app\helpers\ArrayHelper::getColumn($allNodes, 'id')));
$childDataGraph = getDataForGraph($childsNodes, $edgesGateway->getByNodes(\app\helpers\ArrayHelper::getColumn($childsNodes, 'id')));
$parentDataGraph = getDataForGraph($parentNodes, $edgesGateway->getByNodes(\app\helpers\ArrayHelper::getColumn($parentNodes, 'id')));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="assets/js/arbor.js"></script>
    <script src="assets/js/renderer.js"></script>
    <script src="assets/js/graphics.js"></script>
</head>

<style type="text/css">
    div.graph-container {
        border: 1px solid gray;
    }

    div.graph-container.two-column {
        width: 48%;
        display: inline-block;
        margin: 10px;
    }

    div.graph-container h2 {
        text-align: center;
    }
</style>

<body>

<div class="graph-container">
    <h2>All nodes</h2>
    <canvas id="viewport" width="1024" height="500"></canvas>
</div>

<div class="graph-container two-column">
    <h2>Childs "D" node</h2>
    <canvas id="viewport2" width="600" height="500"></canvas>
</div>

<div class="graph-container two-column">
    <h2>Parents "G" node</h2>
    <canvas id="viewport3" width="600" height="500"></canvas>
</div>

<script src="assets/js/graph.js"></script>

<script type="text/javascript">
    var graphRenderer = new GraphRenderer("#viewport");
    graphRenderer.render(<?=json_encode($allNodesGraph)?>);

    var graphRenderer2 = new GraphRenderer("#viewport2");
    graphRenderer2.render(<?=json_encode($childDataGraph)?>);

    var graphRenderer3 = new GraphRenderer("#viewport3");
    graphRenderer3.render(<?=json_encode($parentDataGraph)?>);
</script>
</body>
</html>
