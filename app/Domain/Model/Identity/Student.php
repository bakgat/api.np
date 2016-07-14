<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:23
 */

namespace App\Domain\Model\Identity;

use App\Domain\Model\Time\DateRange;
use \DateTime;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @ORM\Entity
 * @ORM\Table(name="students")
 *
 * Class Student
 * @package App\Domain\Model\Person
 */
class Student extends Person implements \JsonSerializable
{

    /**
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="student", cascade={"persist"})
     *
     * @var StudentInGroup[]
     */
    protected $studentInGroups;


    public function __construct($firstName, $lastName, $email, Gender $gender, DateTime $birthday = null)
    {
        parent::__construct($firstName, $lastName, $email, $gender, $birthday);

        $this->studentInGroups = [];
    }

    /**
     *
     * @param $group
     * @param DateTime $start
     * @param DateTime $end
     * @return $this
     */
    public function joinGroup(Group $group, $start = null, $end = null)
    {
        if ($start == null) {
            $start = new DateTime;
        }
        $studentGroup = new StudentInGroup($this, $group, ['start' => $start, 'end' => $end]);
        $this->studentInGroups[] = $studentGroup;
        return $this;
    }

    /**
     * @param Group $group
     * @param DateTime|null $end
     */
    public function leaveGroup(Group $group, $end = null)
    {
        $id = $group->getId();
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->getGroup()->getId() == $id) {
                $studentInGroup->leaveGroup($end);
            }
        }
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            $groups[] = $studentInGroup->getGroup();
        }
        return $groups;
    }

    /**
     * @return Group[]
     */
    public function getActiveGroups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->isActive()) {
                $groups[] = $studentInGroup->getGroup();
            }
        }
        return $groups;
    }


    /**
     * @param Group $group
     * @param DateTime $date
     * @return bool
     */
    public function wasActiveInGroupAt(Group $group, DateTime $date)
    {
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->getGroup()->getId() == $group->getId()
                && $studentInGroup->wasActiveAt($date)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Group $group
     * @param DateRange $dateRange
     * @return bool
     */
    public function wasActiveInGroupBetween(Group $group, DateRange $dateRange)
    {
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->getGroup()->getId() == $group->getId()
                && $studentInGroup->wasActiveBetween($dateRange)
            ) {
                return true;
            }
        }
        return false;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id' => (string)$this->getId(),
            'gender' => $this->getGender(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'displayName' => $this->getDisplayName(),
            'username' => $this->getEmail(),
            'birthday' => $this->getBirthday()->format('Y-m-d'),
            'classGroups' => $this->getActiveGroups(),
        ];
    }

    private function getStudentInGroups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            $groups[] = $studentInGroup;
        }
        return $groups;
    }
}