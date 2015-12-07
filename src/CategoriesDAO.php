<?php

/**
 * User: Samuil
 * Date: 02-12-2015
 * Time: 10:40 PM
 */
class CategoriesDAO
{

    const MONGO_HOST = "mongodb://localhost:27017";
	const DATABASE_NAME = "ss-category";
    const COLLECTION_NAME = "categories";

    private static $collection;
    private static $connection;

    public function __construct()
    {
    }

    private function connect()
    {
        if (!isset(self::$collection)) {
            self::$connection = new MongoClient(self::MONGO_HOST);
            $db = self::$connection->selectDb(self::DATABASE_NAME);
            self::$collection = $db->selectCollection(self::COLLECTION_NAME);
            self::$collection->createIndex(array('name' => 1), array('unique' => 1, 'dropDups' => 1));
        }
        return true;
    }

    public static function getAll()
    {
		self::connect();
        $categories = self::$collection->find();
        $result = array();
        foreach ($categories as $category) {
            $result['categories'][] = $category;
        }
        self::closeConnection();
        return $result;
    }

    public static function getOne($id)
    {
		self::connect();
        $criteria = array('_id' => new MongoId($id));
        $result = self::$collection->findOne($criteria);
        self::closeConnection();
        return $result;
    }

    public static function update($id, $newDoc)
    {
		self::connect();
        $criteria = array('_id' => new MongoId($id));
        unset($newDoc['_id']);
        $result = self::$collection->update($criteria, array('$set' => json_decode($newDoc)));
        self::closeConnection();
        return $result;
    }

    public static function create($doc)
    {
		self::connect();
        $result = self::$collection->insert(json_decode($doc));
        self::closeConnection();
        return array('success' => 'created');;
    }

    public static function delete($id)
    {
		self::connect();
        $criteria = array('_id' => new MongoId($id));
        self::$collection->remove(
            $criteria,
            array(
                'safe' => true
            )
        );
        self::closeConnection();
        return array('success' => 'deleted');
    }

    private function closeConnection()
    {
        if (self::$connection != null) {
            self::$connection->close();
        }
    }
}
