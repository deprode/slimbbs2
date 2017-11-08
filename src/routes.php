<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slimbbs '/' route");

    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();

    $args['nameKey'] = $nameKey;
    $args['valueKey'] = $valueKey;
    $args['name'] = $request->getAttribute($nameKey);
    $args['value'] = $request->getAttribute($valueKey);

    // Render index view
    return $this->view->render($response, 'index.twig', $args);
});
