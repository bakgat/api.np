<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 13:35
 */

namespace App\Domain\Mapper\Identity;


use App\Domain\Mapper\AbstractDataMapper;
use App\Domain\Model\Identity\Student;
use App\NotosDatabase\DatabaseAdapterInterface;

class StudentMapper extends AbstractDataMapper implements StudentMapperInterface
{
    protected $entityTable = 'students';

    public function __construct(DatabaseAdapterInterface $adapter)
    {
        parent::__construct($adapter);
    }

    public function findById($id)
    {
        // TODO: Implement findById() method.
    }

    public function findAll(array $conditions = [])
    {
        // TODO: Implement findAll() method.
    }

    public function insert(Student $student)
    {
        // TODO: Implement insert() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    protected function createEntity(array $row)
    {
        $student = new Student(
            $row['first_name'],
            $row['last_name'],
            $row['email']
        );
    }
}