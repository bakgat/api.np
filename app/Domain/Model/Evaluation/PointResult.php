<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/08/16
 * Time: 10:12
 */

namespace App\Domain\Model\Evaluation;


use Doctrine\ORM\Mapping AS ORM;

use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Identity\Student;
use App\Domain\Uuid;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;

/**
 * @ORM\Entity
 * @ORM\Table(name="point_results")
 *
 * Class PointResult
 * @package App\Domain\Model\Evaluation
 */
class PointResult
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Evaluation\Evaluation", inversedBy="results")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Evaluation
     */
    protected $evaluation;

    /**
     * @Groups({"evaluation_detail"})
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Student")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @Groups({"evaluation_detail"})
     *
     * @ORM\Column(type="float")
     *
     * @var float
     */
    protected $score;

    /**
     * @Accessor(getter="getRedicodi",setter="setRedicodi")
     * @Groups({"evaluation_detail"})
     *
     * @ORM\Column(type="string")
     * @var string[]
     *
     */
    protected $redicodi;

    public function __construct(Student $student, $score, $redicodi = [])
    {
        $this->id = Uuid::generate(4);
        $this->student = $student;
        $this->score = $score;
        $this->redicodi = implode(',', $redicodi);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEvaluation()
    {
        return $this->evaluation;
    }

    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function getScore()
    {
        return $this->score;
    }


    public function getRedicodi()
    {
        return explode(',', $this->redicodi);
    }

    public function setRedicodi(array $redicodi)
    {
        $this->redicodi = implode(',', $redicodi);
    }
}