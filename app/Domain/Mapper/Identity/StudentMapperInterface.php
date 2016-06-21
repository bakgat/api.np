<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 13:29
 */

namespace App\Domain\Mapper\Identity;


use App\Domain\Model\Identity\Student;

interface StudentMapperInterface
{
    public function findById($id);
    public function findAll(array $conditions = []);

    public function insert(Student $student);
    public function delete($id);
}