<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 12:19
 */

namespace App\NotosDatabase;


interface DatabaseAdapterInterface
{
    public function connect();

    public function disconnect();

    public function prepare($sql, array $options = []);

    public function execute(array $parameters = []);

    public function fetch($fetchStyle = null,
                          $cursorOrientation = null, $cursorOffset = null);

    public function fetchAll($fetchStyle = null, $column = 0);

    public function select($table, array $bind, $boolOperator = 'AND');

    public function insert($table, array $bind);

    public function update($table, array $bind, $where = "");

    public function delete($table, $where = "");
}