<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 12/10/16
 * Time: 15:28
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
 * @ORM\Table(name="multiplechoice_results")
 *
 * Class MultiplechoiceResult
 * @package App\Domain\Model\Evaluation
 */
class MultiplechoiceResult
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Evaluation\Evaluation")
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
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $selected;

    public function __construct(Student $student, $selected=null)
    {
        $this->id = NtUid::generate(4);
        $this->student = $student;
        $this->selected = $selected;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
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
    public function getSelected()
    {
        return $this->selected;
    }

    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

}