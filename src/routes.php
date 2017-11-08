<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {
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

$app->post('/', function (Request $request, Response $response) {
    // Sample log message
    $this->logger->info("Slimbbs '/' route");
    $args = [];

    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();

    $args['nameKey'] = $nameKey;
    $args['valueKey'] = $valueKey;
    $args['name'] = $request->getAttribute($nameKey);
    $args['value'] = $request->getAttribute($valueKey);

    if ($request->getAttribute('csrf_status') === "bad_request") {
        $response = $response->withStatus(403);
        $this->view->render($response, 'index.twig', $args);
        return $response;
    }
    $data = $request->getParsedBody();

    $args['body'] = $data['body'];

    // Render index view
    return $this->view->render($response, 'index.twig', $args);
});