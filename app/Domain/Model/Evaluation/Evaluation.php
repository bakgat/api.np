<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/08/16
 * Time: 09:57
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Identity\Student;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\NtUid;
use DateTime;

use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\Type;

/**
 * @AccessorOrder("custom", custom = {"id", "title", "date", "permanent", "branchForGroup", "max", "average", "median"})
 * @ORM\Entity
 * @ORM\Table(name="evaluations")
 *
 * Class Evaluation
 * @package App\Domain\Model\Evaluation
 */
class Evaluation
{
    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     *
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Education\BranchForGroup")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var BranchForGroup
     */
    protected $branchForGroup;


    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date")
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $title;

    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $permanent;

    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $final;

    /**
     * @Groups({"group_evaluations", "evaluation_detail"})
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $max;


    /**
     * @Groups({"p_evaluation_detail"})
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Evaluation\PointResult", mappedBy="evaluation", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $pointResults;

    /**
     * @Groups({"c_evaluation_detail"})
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Evaluation\ComprehensiveResult", mappedBy="evaluation", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $comprehensiveResults;

    /**
     * @Groups({"s_evaluation_detail"})
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Evaluation\ComprehensiveResult", mappedBy="evaluation", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $spokenResults;
    /**
     * Evaluation constructor.
     * @param BranchForGroup $branchForGroup
     * @param $title
     * @param null $date
     * @param null $max
     * @param bool $permanent
     * @param bool $final
     */
    public function __construct(BranchForGroup $branchForGroup, $title, $date = null, $max = null, $permanent = true, $final = false)
    {
        $this->id = NtUid::generate(4);
        $this->branchForGroup = $branchForGroup;
        $this->title = $title;
        $this->permanent = $permanent;
        $this->final = $final;
        $this->max = $max;
        if ($date == null) {
            $date = new DateTime;
        }
        $this->date = $date;
        $this->pointResults = new ArrayCollection;
        $this->comprehensiveResults = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBranchForGroup()
    {
        return $this->branchForGroup;
    }

    /**
     * @return Branch
     */
    public function getBranch()
    {
        return $this->branchForGroup->getBranch();
    }

    public function getGroup()
    {
        return $this->branchForGroup->getGroup();
    }

    /**
     * @VirtualProperty
     * @Groups({"group_evaluations", "evaluation_detail"})
     *
     * @return EvaluationType
     */
    public function getEvaluationType()
    {
        return $this->branchForGroup->getEvaluationType();
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function isPermanent()
    {
        return $this->permanent;
    }

    public function isFinal()
    {
        return $this->final;
    }

    public function getMax()
    {
        return $this->max;
    }

    /**
     * @VirtualProperty
     * @Groups({"group_evaluations", "evaluation_detail"})
     *
     * @return float
     */
    public function getAverage()
    {
        return collection_average($this->pointResults, 'score');
    }

    /**
     * @VirtualProperty
     * @Groups({"group_evaluations", "evaluation_detail"})
     *
     * @return float
     */
    public function getMedian()
    {
        return collection_median($this->pointResults, 'score');
    }

    /** ArrayCollection is not accessible */
    public function getPointResults()
    {
        return clone $this->pointResults;
    }

    public function getComprehensiveResults()
    {
        return clone $this->comprehensiveResults;
    }

    public function update($title, $branchForGroup, $date, $max, $permanent, $final)
    {
        $this->title = $title;
        $this->branchForGroup = $branchForGroup;
        $this->date = $date;
        $this->max = $max;
        $this->permanent = $permanent;
        $this->final = $final;
        return $this;
    }

    public function addPointResult(PointResult $result)
    {
        $this->pointResults->add($result);
        $result->setEvaluation($this);
    }

    public function updatePointResult(Student $student, $score, $redicodi)
    {
        /** @var PointResult $result */
        foreach ($this->pointResults as $result) {
            if ($result->getStudent()->getId() == $student->getId()) {
                $result->update($score, $redicodi);
                break;
            }
        }

        return $this;
    }

    public function addComprehensiveResult(ComprehensiveResult $result)
    {
        $this->comprehensiveResults->add($result);
        $result->setEvaluation($this);
    }


    //TODO: update comprehensive results

}