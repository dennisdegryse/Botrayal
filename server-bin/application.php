<?php

define("DIR_ROOT", __DIR__ . '/../');

$loader = require_once DIR_ROOT . 'vendor/autoload.php';
$app = new \Slim\Slim();

$app->get('/game/list', function() 
{
	// Not implemented
});

$app->get('/game/:id', function($id) 
{
	// Not implemented
});

$app->post('/game/:id/:action', function($action) 
{
	// Not implemented
});

$app->run();