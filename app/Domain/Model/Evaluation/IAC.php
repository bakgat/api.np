<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/08/16
 * Time: 19:53
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Goal;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Time\DateRange;
use App\Domain\Model\Time\DateRangeTrait;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;


use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="iacs")
 *
 * Class IAC
 * @package App\Domain\Model\Evaluation
 */
class IAC
{
    use DateRangeTrait;

    /**
     * @Groups({"student_iac"})
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @Groups({"student_iac"})
    *
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Student")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @Groups({"student_iac"})
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Education\Branch")
     *
     * @var Branch
     */
    protected $branch;

    /**
     * @Groups({"student_iac"})
     * @ORM\OneToMany(targetEntity="IACGoal", mappedBy="iac", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var IACGoal[]
     */
    protected $iacGoals;

    /**
     * @Groups({"student_iac"})
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    protected $dateRange;

    public function __construct(Student $student, Branch $branch, $dateRange)
    {
        $this->id = NtUid::generate(4);
        $this->student = $student;
        $this->branch = $branch;
        $this->iacGoals = new ArrayCollection;
        $this->dateRange = DateRange::fromData($dateRange);
    }

    public function getId()
    {
        return $this->id;
    }


    public function getStudent()
    {
        return $this->student;
    }

    public function addGoal(Goal $goal, $date = null)
    {
        $iacGoal = new IACGoal($this, $goal, $date);
        $this->iacGoals->add($iacGoal);
        return $iacGoal;
    }

    public function allIACGoals()
    {
        return clone $this->iacGoals;
    }

    /**
     * @param IACGoal $item
     */
    public function removeIACGoal(IACGoal $item)
    {
        $this->iacGoals->removeElement($item);
    }

    public function setDateRange($dateRange)
    {
        $this->dateRange = DateRange::fromData($dateRange);
        return $this;
    }
}