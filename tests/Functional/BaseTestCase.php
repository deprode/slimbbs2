<?php

namespace Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

use Dotenv\Dotenv;

/**
 * Class BaseTestCase
 *
 * @package Tests\Functional
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = false;

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    public function runApp($requestMethod, $requestUri, $requestData = null, $isHxr = false)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        $env_file = __DIR__. '/../.env';
        if (is_readable($env_file)) {
            $dot_env = new Dotenv(__DIR__ . '/../');
            $dot_env->load();
        }
        putenv('MYSQL_HOST='.getenv('MYSQL_LOCAL_HOST'));

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        if ($isHxr === true) {
            $request = $request->withHeader('X-Requested-With', 'XMLHttpRequest');
        }

        // Set up a response object
        $response = new Response();

        // Use the application settings
        $settings = require __DIR__ . '/../../src/settings.php';

        // Instantiate the application
        $app = new App($settings);

        // Set up dependencies
        require __DIR__ . '/../../src/dependencies.php';

        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__ . '/../../src/middleware.php';
        } else {
            $request = $request->withAttribute('userId', $_SESSION['user_id']);
            $request = $request->withAttribute('adminId', $_SESSION['admin_id']);
            $request = $request->withAttribute('isAdmin', (int)($_SESSION['user_id'] == $_SESSION['admin_id']));
            $request = $request->withAttribute('isLoggedIn', (int)($_SESSION['user_id'] != 0));
        }

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    public function setUp()
    {
        parent::setUp();

        $env_file = __DIR__. '/../.env';
        if (is_readable($env_file)) {
            $dot_env = new Dotenv(__DIR__ . '/../');
            $dot_env->load();
        }
        putenv('MYSQL_HOST='.getenv('MYSQL_LOCAL_HOST'));
    }
}
