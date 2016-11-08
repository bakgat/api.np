<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/08/16
 * Time: 19:54
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\Goal;
use App\Domain\NtUid;
use DateTime;


use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="iac_goals")
 *
 * Class IACGoal
 * @package App\Domain\Model\Evaluation
 */
class IACGoal
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="IAC", inversedBy="iacGoals")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var IAC
     */
    protected $iac;

    /**
     * @Groups({"student_iac"})
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Education\Goal")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Goal
     */
    protected $goal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $achieved;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var bool
     */
    protected $practice;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $comment;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @var DateTime
     */
    protected $date;


    public function __construct(IAC $iac, Goal $goal, $date = null)
    {
        $this->id = NtUid::generate(4);
        $this->iac = $iac;
        $this->goal = $goal;

        if ($date == null) {
            $this->date = new DateTime;
        } else {
            $this->date = $date;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIac()
    {
        return $this->iac;
    }

    public function getGoal()
    {
        return $this->goal;
    }

    public function isAchieved()
    {
        return $this->achieved;
    }

    public function isPractice()
    {
        return $this->practice;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setAchieved($comment = null)
    {
        $this->achieved = true;
        $this->practice = false;
        $this->comment = $comment;
        $this->date = new DateTime;

        return $this;
    }
    public function setPractice($comment = null) {
        $this->achieved = false;
        $this->practice = true;
        $this->comment = $comment;
        $this->date = new DateTime();

        return $this;
    }
}