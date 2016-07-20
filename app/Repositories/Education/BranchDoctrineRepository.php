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
use App\Domain\Model\Education\Exceptions\BranchNotFoundException;
use App\Domain\Model\Education\Exceptions\MajorNotFoundException;
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
            ->from(Branch::class, 'b')
            ->join('b.major', 'm')
            ->join('b.branchForGroups', 'bfg')
            ->where('bfg.group=?1')
            ->setParameter(1, $group->getId());
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a branch by its id, if not returns null.
     * @param $id
     * @return Branch|null
     */
    public function findBranch(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('b')
            ->from(Branch::class, 'b')
            ->where('b.id=?1')
            ->setParameter(1, $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Gets an existing Branch by its id.
     *
     * @param Uuid $id
     * @return Branch
     *
     * @throws BranchNotFoundException
     */
    public function getBranch(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('b')
            ->from(Branch::class, 'b')
            ->where('b.id=?1')
            ->setParameter(1, $id);

        $branch = $qb->getQuery()->getOneOrNullResult();

        if ($branch == null) {
            throw new BranchNotFoundException($id);
        }

        return $branch;
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
        $qb = $this->em->createQueryBuilder();
        $qb->select('b')
            ->from(Branch::class, 'b')
            ->join('b.branchForGroup', 'bfg')
            ->where('b.major=?1')
            ->andWhere('bfg.group=?2')
            ->setParameter(1, $major->getId())
            ->setParameter(2, $group->getId());

        return $qb->getQuery()->getResult();

    }

    /**
     * Gets all the active major in a group.
     *
     * @param Group $group
     * @return ArrayCollection|Major[]
     */
    public function allMajors(Group $group)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m')
            ->from(Major::class, 'm')
            ->join('m.branches', 'b')
            ->join('b.branchForGroups', 'bfg')
            ->where('bfg.group=?1')
            ->setParameter(1, $group->getId());
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a major by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Major|null
     */
    public function findMajor(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m')
            ->from(Major::class, 'm')
            ->where('m.id=?1')
            ->setParameter(1, $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Gets an existing major by its id.
     *
     * @param Uuid $id
     * @return Major
     * @throws MajorNotFoundException
     */
    public function getMajor(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m')
            ->from(Major::class, 'm')
            ->where('m.id=?1')
            ->setParameter(1, $id);

        $major = $qb->getQuery()->getOneOrNullResult();

        if ($major == null) {
            throw new MajorNotFoundException($id);
        }

        return $major;
    }

    /**
     * Saves a new Major
     *
     * @param Major $major
     * @return Uuid
     */
    public function insertMajor(Major $major) {
        $this->em->persist($major);
        $this->em->flush();
        return $major->getId();
    }
    /**
     * Saves a new Branch
     *
     * @param Branch $branch
     * @return Uuid
     */
    public function insertBranch(Branch $branch)
    {
        $this->em->persist($branch);
        $this->em->flush();
    }

    /**
     * Saves an existing branch.
     *
     * @param Branch $branch
     * @return int Number of affected rows.
     */
    public function update(Branch $branch)
    {
        $this->em->persist($branch);
        $this->em->flush();
    }

    /**
     * Deletes an existing Branch.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function deleteBranch(Uuid $id)
    {
        $branch = $this->getBranch($id);
        $this->em->remove($branch);
        $this->em->flush();
        return 1;
    }

    /**
     * Deletes an existing Major.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function deleteMajor(Uuid $id)
    {
        $major = $this->getMajor($id);
        $this->em->remove($major);
        $this->em->flush();
        return 1;
    }
}