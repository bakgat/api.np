<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 13:25
 */

namespace App\Domain\Mapper;


use App\NotosDatabase\DatabaseAdapterInterface;

abstract class AbstractDataMapper
{
    protected $adapter;
    protected $entityTable;

    public function __construct(DatabaseAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function findById($id)
    {
        $this->adapter->select($this->entityTable, ['id' => $id]);

        if (!$row = $this->adapter->fetch()) {
            return null;
        }

        return $this->createEntity($row);
    }

    public function findAll(array $conditions = [])
    {
        $entities = [];
        $this->adapter->select($this->entityTable, $conditions);
        $rows = $this->adapter->fetchAll();

        if ($rows) {
            foreach ($rows as $row) {
                $entities[] = $this->createEntity($row);
            }
        }
        return $entities;
    }

    abstract protected function createEntity(array $row);
}