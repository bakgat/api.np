<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 09:44
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\Collection;
use App\Domain\Model\Identity\Exceptions\StaffNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

class StaffDoctrineRepository implements StaffRepository
{
    /** @var  EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get all staff members.
     *
     * @return ArrayCollection
     */
    public function all()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(Staff::class, 's')
            ->orderBy('s.lastName');
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a staff member by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Staff|null
     */
    public function find(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig', Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sig.dateRange.start', '?1'),
                    $qb->expr()->gte('sig.dateRange.end', '?1')
                ))
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr', Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sr.dateRange.start', '?1'),
                    $qb->expr()->gte('sr.dateRange.end', '?1')
                ))
            ->leftJoin('sr.role', 'r')
            ->where('s.id=?2')
            ->setParameter(1, new DateTime)
            ->setParameter(2, $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Finds a staff member by its email address, if not returns null.
     *
     * @param $email
     * @return Staff|null
     */
    public function findByEmail($email)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig', \Doctrine\ORM\Query\Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sig.dateRange.start', '?1'),
                    $qb->expr()->gte('sig.dateRange.end', '?1')
                ))
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr', Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sr.dateRange.start', '?1'),
                    $qb->expr()->gte('sr.dateRange.end', '?1')
                ))
            ->leftJoin('sr.role', 'r')
            ->where('s.email=?2')
            ->setParameter(1, new DateTime)
            ->setParameter(2, $email);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Gets an existing staff member by its id.
     *
     * @param NtUid $id
     * @return Staff
     * @throws StaffNotFoundException
     */
    public function get(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig', \Doctrine\ORM\Query\Expr\Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sig.dateRange.start', '?1'),
                    $qb->expr()->gte('sig.dateRange.end', '?1')
                ))
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr')
            /*, Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sr.dateRange.start', '?1'),
                    $qb->expr()->gte('sr.dateRange.end', '?1')
                )
            */
            ->leftJoin('sr.role', 'r')
            ->where('s.id=?2')
            ->setParameter(1, new DateTime)
            ->setParameter(2, $id);
        $staff = $qb->getQuery()->getOneOrNullResult();

        if ($staff == null) {
            throw new StaffNotFoundException($id);
        }

        return $staff;
    }

    /**
     * Saves a new staff member.
     *
     * @param Staff $staff
     * @return NtUid
     */
    public function insert(Staff $staff)
    {
        $this->em->persist($staff);
        $this->em->flush();
        return $staff->getId();
    }

    /**
     * Saves an existing staff member.
     *
     * @param Staff $staff
     * @return int Number of affected rows.
     */
    public function update(Staff $staff)
    {
        $this->em->persist($staff);
        $this->em->flush();
        return 1;
    }

    /**
     * Deletes an existing staff member.
     *
     * @param NtUid $id
     * @return int Number of affected rows.
     */
    public function delete(NtUid $id)
    {
        $staff = $this->get($id);
        $this->em->remove($staff);
        $this->em->flush();
        return 1;
    }


}