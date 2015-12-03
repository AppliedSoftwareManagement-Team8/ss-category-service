<?php
require '../vendor/autoload.php';
require '../src/CategoriesDAO.php';
require '../src/JsonResponse.php';

use \Slim\Middleware;

// Prepare app
$app = new \Slim\Slim();
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
    $categories = new CategoriesDAO();
    try {
        $app->response->setBody(json_encode($categories->connect()->getOne($id)));
        return $app->response->getBody();
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody('{"error":{"message":' . $e->getMessage() . '}}');
        return json_encode($app->response->getBody());
    }
}

function getAllCategories () {
    $app = \Slim\Slim::getInstance();
    $categories = new CategoriesDAO();
    try {
        $app->response->setBody(json_encode($categories->connect()->getAll()));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody('{"error":{"message":' . $e->getMessage() . '}}');
        return json_encode($app->response->getBody());
    }
}

function createCategory () {
    $app = \Slim\Slim::getInstance();
    $categories = new CategoriesDAO();
    try {
        $app->response->setBody(json_encode($categories->connect()->create($app->request->getBody())));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody('{"error":{"message":' . $e->getMessage() . '}}');
        return json_encode($app->response->getBody());
    }
}

function updateCategoryById ($id) {
    $app = \Slim\Slim::getInstance();
    $categories = new CategoriesDAO();
	$data = json_decode($app->request->getBody());
	if (!array_key_exists( 'name', $data ) || !array_key_exists ( 'description', $data )) {
		if(!isset($data['name']) || !isset($data['description'])) {
			$app->halt(422, json_encode(array('status' => 422, 'error' => 'missing or undefined parameters')));
		}
	}
    try {
        $app->response->write(json_encode($categories->connect()->update($id, $app->request->getBody())));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody('{"error":{"message":' . $e->getMessage() . '}}');
        return json_encode($app->response->getBody());
    }
}

function deleteCategoryById ($id) {
    $app = \Slim\Slim::getInstance();
    $categories = new CategoriesDAO();
    try {
        $app->response->write(json_encode($categories->connect()->delete($id)));
        return json_encode($app->response->getBody());
    } catch (Exception $e) {
        $app->response->setStatus(404);
        $app->response->setBody('{"error":{"message":' . $e->getMessage() . '}}');
        return json_encode($app->response->getBody());
    }
}

function reqDataCheck() {
	$app = \Slim\Slim::getInstance();
	$data = json_decode($app->request->getBody(), true);
	if (array_key_exists( 'name', $data ) && array_key_exists ( 'description', $data )) {
		if(isset($data['name']) && isset($data['description'])) {
			if(empty($data['name']) || empty($data['description'])) {
				$app->halt(422, json_encode(array('status' => 422, 'error' => 'missing or undefined parameters')));
			}
		} else {
			$app->halt(422, json_encode(array('status' => 422, 'error' => 'missing or undefined parameters')));
		}
	} else {
		$app->halt(422, json_encode(array('status' => 422, 'error' => 'missing or undefined parameters')));
	}
}

// Define routes
$app->group('/api', function () use ($app) {
    $app->group('/categories', function () use ($app) {

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
});

// Run app
$app->run();