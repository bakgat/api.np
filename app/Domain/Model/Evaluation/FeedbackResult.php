<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 16/11/16
 * Time: 20:38
 */

namespace App\Domain\Model\Evaluation;

use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;
use Doctrine\ORM\Mapping AS ORM;


use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="feedback_results")
 *
 * Class FeedbackResult
 * @package app\Domain\Model\Evaluation
 */
class FeedbackResult
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Evaluation\Evaluation", inversedBy="feedbackResults")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Evaluation
     */
    protected $evaluation;

    /**
     * @Groups({"evaluation_detail"})
     *
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Student")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @Groups({"evaluation_detail"})
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $summary;

    public function __construct(Student $student, $summary = null)
    {
        $this->id = NtUid::generate(4);
        $this->student = $student;
        $this->summary = $summary;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Evaluation $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return Evaluation
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @return Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    
}