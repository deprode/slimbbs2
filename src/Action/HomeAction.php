<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard as Csrf;
use Slim\Views\Twig;

final class HomeAction
{
    private $logger;
    private $csrf;
    private $view;

    public function __construct(LoggerInterface $logger, Csrf $csrf, Twig $view)
    {
        $this->logger = $logger;
        $this->csrf = $csrf;
        $this->view = $view;
    }

    public function index(Request $request, Response $response)
    {
        $this->logger->info("Slimbbs '/' route");

        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $args['nameKey'] = $nameKey;
        $args['valueKey'] = $valueKey;
        $args['name'] = $request->getAttribute($nameKey);
        $args['value'] = $request->getAttribute($valueKey);

        // Render index view
        return $this->view->render($response, 'index.twig', $args);
    }
}