<?php

use Stone\Maze\Grid;

require_once 'bootstrap.php';
$start = microtime(true);
$main = new Grid('input.txt');
$main->setStartPoint(0, 0);
$main->setEndPoint($main->height - 1, $main->width - 1);
$steps = $main->getPath();
$end = microtime(true);
$time = $end - $start;
$sequence = join(' ', $steps);
file_put_contents('response.txt', $sequence);
echo "Tempo de execução: $time segundos" . PHP_EOL;
