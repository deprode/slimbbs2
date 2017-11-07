<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slimbbs '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
