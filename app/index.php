<?php
/**
 * @file Initializes the Silex app and provides routing.
 */

use Symfony\Component\HttpFoundation\Request;

// Load the necessary classes and initialize the app.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ .'/FrontPage.php';
require_once __DIR__ .'/FavController.php';
$app = new Silex\Application();


// Load the database configuration into the $app service container.
require_once __DIR__ . '/../config.php';

// Add the database connection as a shared service to the container.
$db_string = 'mysql:host=' . $app['db.hostname'] .';dbname=' . $app['db.dbname'];
$app['db'] = $app->share(function ($app) use ($db_string) {
  try {
    return new PDO($db_string, $app['db.username'], $app['db.password']);
  }
  catch (PDOException $e) {
    $app->abort(500, 'Unable to connect to database.  Check your configuration');
  }
});

// Register the Twig service with the container.
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));


/**
 * If the content type of this request is json, convert the payload into request
 * parameters.
 */
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

/**
 * Route Definitions
 */
// Front Page
$app->get('/', 'frontPage');

// Create Favorite
$app->post('/fav', 'FavController::create');

// List Favorites
$app->get('/fav', 'FavController::getList');

// Read Favorite
$app->get('/fav/{id}', 'FavController::read');

// Update Favorite
$app->put('/fav/{id}', 'FavController::update');

// Delete Favorite
$app->delete('/fav/{id}', 'FavController::delete');

// General Error Handler.
$app->error(function (Exception $e, $code) {
  return $e->getMessage();
});

$app->run();