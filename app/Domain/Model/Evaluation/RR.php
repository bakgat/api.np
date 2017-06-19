<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/17
 * Time: 10:03
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rr")
 * Class RR
 * @package App\Domain\Model\Evaluation
 */
class RR
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Education\BranchForGroup")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var BranchForGroup
     */
    protected $branchForGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Evaluation\GraphRange")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var GraphRange
     */
    protected $graphRange;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Student")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @ORM\Column(type="float", name="p_raw")
     *
     * @var float
     */
    protected $permanentRaw;

    /**
     * @ORM\Column(type="float", name="e_raw")
     *
     * @var float
     */
    protected $endRaw;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    protected $total;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    protected $max;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $redicodi;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $evaluationCount;

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param NtUid $id
     * @return RR
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return BranchForGroup
     */
    public function getBranchForGroup()
    {
        return $this->branchForGroup;
    }

    /**
     * @param BranchForGroup $branchForGroup
     * @return RR
     */
    public function setBranchForGroup($branchForGroup)
    {
        $this->branchForGroup = $branchForGroup;
        return $this;
    }

    /**
     * @return GraphRange
     */
    public function getGraphRange()
    {
        return $this->graphRange;
    }

    /**
     * @param GraphRange $graphRange
     * @return RR
     */
    public function setGraphRange($graphRange)
    {
        $this->graphRange = $graphRange;
        return $this;
    }

    /**
     * @return Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param Student $student
     * @return RR
     */
    public function setStudent($student)
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return float
     */
    public function getPermanentRaw()
    {
        return $this->permanentRaw;
    }

    /**
     * @param float $permanentRaw
     * @return RR
     */
    public function setPermanentRaw($permanentRaw)
    {
        $this->permanentRaw = $permanentRaw;
        return $this;
    }

    /**
     * @return float
     */
    public function getEndRaw()
    {
        return $this->endRaw;
    }

    /**
     * @param float $endRaw
     * @return RR
     */
    public function setEndRaw($endRaw)
    {
        $this->endRaw = $endRaw;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return RR
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param float $max
     * @return RR
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return string
     */
    public function getRedicodi()
    {
        return $this->redicodi;
    }

    /**
     * @param string $redicodi
     * @return RR
     */
    public function setRedicodi($redicodi)
    {
        $this->redicodi = $redicodi;
        return $this;
    }

    /**
     * @return int
     */
    public function getEvaluationCount()
    {
        return $this->evaluationCount;
    }

    /**
     * @param int $evaluationCount
     * @return RR
     */
    public function setEvaluationCount($evaluationCount)
    {
        $this->evaluationCount = $evaluationCount;
        return $this;
    }


}