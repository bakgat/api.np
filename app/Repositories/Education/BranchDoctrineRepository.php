<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/07/16
 * Time: 21:11
 */

namespace App\Repositories\Education;


use App\Domain\Model\Education\ArrayColleciton;
use App\Domain\Model\Education\ArrayCollection;
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Education\Exceptions\BranchNotFoundException;
use App\Domain\Model\Education\Exceptions\MajorNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\NtUid;
use Doctrine\ORM\EntityManager;

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
     * @param $id
     * @return Branch|null
     */
    public function findBranch(NtUid $id)
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
     * @param NtUid $id
     * @return Branch
     *
     * @throws BranchNotFoundException
     */
    public function getBranch(NtUid $id)
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
     * @param Group $group
     * @return ArrayColleciton
     */
    public function allBranchesInGroup(Group $group)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('bfg, m, b')
            ->from(BranchForGroup::class, 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->andWhere('bfg.group=:group')
            ->setParameter('group', $group->getId());

        return $qb->getQuery()->getResult();
    }


    /**
     * Finds a major by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Major|null
     */
    public function findMajor(NtUid $id)
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
     * @param NtUid $id
     * @return Major
     * @throws MajorNotFoundException
     */
    public function getMajor(NtUid $id)
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
     * @return NtUid
     */
    public function insertMajor(Major $major)
    {
        $this->em->persist($major);
        $this->em->flush();
        return $major->getId();
    }

    /**
     * Saves a new Branch
     *
     * @param Branch $branch
     * @return NtUid
     */
    public function insertBranch(Branch $branch)
    {
        $this->em->persist($branch);
        $this->em->flush();
    }

    /**
     * Saves an existing major.
     *
     * @param Major $major
     * @return int Number of affected rows.
     */
    public function update(Major $major)
    {
        $this->em->persist($major);
        $this->em->flush();
        return 1;
    }

    /**
     * Deletes an existing Branch.
     *
     * @param NtUid $id
     * @return int Number of affected rows.
     */
    public function deleteBranch(NtUid $id)
    {
        $branch = $this->getBranch($id);
        $this->em->remove($branch);
        $this->em->flush();
        return 1;
    }

    /**
     * Deletes an existing Major.
     *
     * @param NtUid $id
     * @return int Number of affected rows.
     */
    public function deleteMajor(NtUid $id)
    {
        $major = $this->getMajor($id);
        $this->em->remove($major);
        $this->em->flush();
        return 1;
    }

    /**
     * @param NtUid $branchForGroupId
     * @return BranchForGroup
     */
    public function getBranchForGroup(NtUid $branchForGroupId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('bfg')
            ->from(BranchForGroup::class, 'bfg')
            ->where('bfg.id=:id')
            ->setParameter('id', $branchForGroupId);
        return $qb->getQuery()->getOneOrNullResult();
    }



}