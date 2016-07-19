<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/07/16
 * Time: 21:11
 */

namespace App\Repositories\Education;


use App\Domain\Model\Education\ArrayCollection;
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\Group;
use Doctrine\ORM\EntityManager;
use Webpatser\Uuid\Uuid;

class BranchDoctrineRepository implements BranchRepository
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Gets all active majors and branches for a group.
     *
     * @param Group $group
     * @return ArrayCollection
     */
    public function all(Group $group)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m, b')
            ->from(Major::class, 'm')
            ->join('m.branches', 'b')
            ->join('b.branchForGroups', 'bfg')
            ->where('bfg.group=?1')
            ->setParameter(1, $group->getId());
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a branch by its id, if not returns null.
     * @param Uuid $id
     * @return Branch|null
     */
    public function findBranch(Uuid $id)
    {
        // TODO: Implement findBranch() method.
    }

    /**
     * Gets an existing Branch by its id.
     *
     * @param Uuid $id
     * @return Branch
     */
    public function getBranch(Uuid $id)
    {
        // TODO: Implement getBranch() method.
    }

    /**
     * Gets all active branches of a major for a group.
     *
     * @param Group $group
     * @param Major $major
     * @return ArrayCollection|Branch[]
     */
    public function allBranches(Group $group, Major $major)
    {
        // TODO: Implement allBranches() method.
    }

    /**
     * Gets all the active major in a group.
     *
     * @param Group $group
     * @return ArrayCollection|Major[]
     */
    public function allMajors(Group $group)
    {
        // TODO: Implement allMajors() method.
    }

    /**
     * Finds a major by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Major|null
     */
    public function findMajor(Uuid $id)
    {
        // TODO: Implement findMajor() method.
    }

    /**
     * Gets an existing major by its id.
     *
     * @param Uuid $id
     * @return Major
     */
    public function getMajor(Uuid $id)
    {
        // TODO: Implement getMajor() method.
    }

    /**
     * Saves a new Branch
     *
     * @param Branch $branch
     * @return Uuid
     */
    public function insert(Branch $branch)
    {
        // TODO: Implement insert() method.
    }

    /**
     * Saves an existing branch.
     *
     * @param Branch $branch
     * @return int Number of affected rows.
     */
    public function update(Branch $branch)
    {
        // TODO: Implement update() method.
    }

    /**
     * Deletes an existing Branch.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function deleteBranch(Uuid $id)
    {
        // TODO: Implement deleteBranch() method.
    }

    /**
     * Deletes an existing Major.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function deleteMajor(Uuid $id)
    {
        // TODO: Implement deleteMajor() method.
    }
}