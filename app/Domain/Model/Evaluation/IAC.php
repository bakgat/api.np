<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/08/16
 * Time: 19:53
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Identity\Student;
use App\Domain\Model\Time\DateRange;
use App\Domain\Model\Time\DateRangeTrait;
use App\Domain\Uuid;
use Doctrine\Common\Collections\ArrayCollection;


use Doctrine\ORM\Mapping AS ORM;

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
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Student")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @ORM\OneToMany(targetEntity="IACGoal", mappedBy="iac", cascade={"persist"})
     *
     * @var IACGoal[]
     */
    protected $iacGoals;

    /**
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    protected $dateRange;

    public function __construct(Student $student, $dateRange)
    {
        $this->id = Uuid::generate(4);
        $this->iacGoals = new ArrayCollection;
        if ($dateRange instanceof DateRange) {
            $this->dateRange = $dateRange;
        } else {
            $this->dateRange = DateRange::fromData($dateRange);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function addGoal(Goal $goal, $date = null) {
        $iacGoal = new IACGoal($this, $goal, $date);
        $this->iacGoals->add($iacGoal);
        return $iacGoal;
    }
}