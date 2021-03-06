<?php
require_once '../vendor/autoload.php';
require_once '../src/CategoriesDAO.php';
require_once '../src/JsonResponse.php';

use \Slim\Middleware;

// Prepare app
$app = new \Slim\Slim();
$corsOptions = array(
    "origin" => "*",
    "maxAge" => 1728000
);
$app->add(new \CorsSlim\CorsSlim($corsOptions));
$app->add(new JsonResponse());
$app->notFound(
    function () use ($app) {
        $app->log->error('Not Found', array('path' => $app->request()->getPath()));
        $app->halt(404, json_encode(array('status' => 404, 'message' => 'not found')));
    }
);

// Create monolog logger and store logger in container as singleton
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('ss-category');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

function getCategoryById ($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->setBody(json_encode(CategoriesDAO::getOne($id)));
        return $app->response->getBody();
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function getAllCategories () {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->setBody(json_encode(CategoriesDAO::getAll()));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function createCategory () {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->setBody(json_encode(CategoriesDAO::create($app->request->getBody())));
        $app->response->setStatus(201);
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function updateCategoryById ($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(CategoriesDAO::update($id, $app->request->getBody())));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}

function deleteCategoryById ($id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->response->write(json_encode(CategoriesDAO::delete($id)));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody(getErrorMessage($e));
        return json_encode($app->response->getBody());
    }
}


function getErrorMessage($exception) {
    return json_encode(array('error'=> array('message'=> $exception->getMessage())));
}

function reqDataCheck() {
	$app = \Slim\Slim::getInstance();
	$data = json_decode($app->request->getBody(), true);
	if (array_key_exists( 'name', $data ) && array_key_exists ( 'description', $data )) {
		if(isset($data['name']) && isset($data['description'])) {
			if(empty($data['name']) || empty($data['description'])) {
				$app->halt(422, json_encode(array('status' => 422, 'error' => 'Empty value parameters')));
			}
		} else {
           $app->halt(422, json_encode(array('status' => 422, 'error' => 'Undefined parameters')));
		   }
    } else {
      $app->halt(422, json_encode(array('status' => 422, 'error' => 'Missing parameters')));
	}
}
// Define routes
$app->group('/api', function () use ($app) {
    // Get a Category
        $app->get('/:id', 'getCategoryById' );

        // Get all Categories
        $app->get('/', 'getAllCategories');

        // Create new Category
        $app->post('/', 'reqDataCheck', 'createCategory');

        // Update a Category by ID
        $app->put('/:id', 'reqDataCheck','updateCategoryById');

        // Delete a Category by ID
        $app->delete('/:id', 'deleteCategoryById');
});

// Run app
$app->run();