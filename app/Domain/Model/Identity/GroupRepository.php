<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 15:25
 */

namespace App\Domain\Model\Identity;


use Webpatser\Uuid\Uuid;

interface GroupRepository
{
    /**
     * Gets all the groups.
     *
     * @return ArrayCollection|Group[]
     */
    public function all();

    /**
     * Finds a group by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Group|null
     */
    public function find(Uuid $id);

    /**
     * Gets an existing group by its id.
     *
     * @param Uuid $id
     * @return Group
     */
    public function get(Uuid $id);

    /**
     * Saves a new group.
     *
     * Note: the name of the group must be unique.
     *
     * @param Group $group
     * @return Uuid
     */
    public function insert(Group $group);

    /**
     * Saves an existing group.
     *
     * @param Group $group
     * @return int Number of affected rows.
     */
    public function update(Group $group);

    /**
     * Deletes an existing group.
     *
     * @param $id
     * @return int Number of affected rows.
     */
    public function delete(Uuid $id);
}