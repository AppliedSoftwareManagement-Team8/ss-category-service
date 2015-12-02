<?php
require '../vendor/autoload.php';
require '../src/CategoriesDAO.php';
require '../src/JsonResponse.php';

use \Slim\Middleware;

// Prepare app
$app = new \Slim\Slim();
$app->add(new JsonResponse());

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

// Define routes
$app->group('/api', function () use ($app) {
    $app->group('/categories', function () use ($app) {

        // Get a Category
        $app->get('/:id', 'getCategoryById' );

        // Get all Categories
        $app->get('/', 'getAllCategories');

        // Create new Category
        $app->post('/', 'createCategory');

        // Update a Category by ID
        $app->put('/:id', 'updateCategoryById');

        // Delete a Category by ID
        $app->delete('/:id', 'deleteCategoryById');
    });
});


// Run app
$app->run();