<?php

/**
 * User: Samuil
 * Date: 02-12-2015
 * Time: 10:40 PM
 */
class CategoriesDAO
{
    protected static $collection;
    protected static $connection;
    const MONGO_HOST = "mongodb://localhost:27017";

    public function __construct()
    {
    }

    public function connect()
    {
        if (!isset(self::$collection)) {
            self::$connection = new MongoClient();
            $db = self::$connection->selectDb("ss-category");
            self::$collection = $db->selectCollection("categories");
            self::$collection->createIndex(array('name' => 1), array('unique' => 1, 'dropDups' => 1));
        }

        if (self::$collection === false) {

            return false;
        }
        return $this;
    }

    public function getAll()
    {
        $categories = self::$collection->find();
        $result = array();
        foreach ($categories as $category) {
            $result[] = $category;
        }
        self::closeConnection();
        return $result;
    }

    public function getOne($id)
    {
        $criteria = array('_id' => new MongoId($id));
        $result = self::$collection->findOne($criteria);
        self::closeConnection();
        $result['_id'] = $result['_id']->{'$id'};
        return $result;
    }

    public function update($id, $newDoc)
    {
        $criteria = array('_id' => new MongoId($id));
        unset($newDoc['_id']);
        $result = self::$collection->update($criteria, array('$set' => json_decode($newDoc)));
        self::closeConnection();
        return $result;
    }

    public function create($doc)
    {
        $result = self::$collection->insert(json_decode($doc));
        self::closeConnection();
        return array('success' => 'created');;
    }

    public function delete($id)
    {
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
        self::$connection->close();
    }
}