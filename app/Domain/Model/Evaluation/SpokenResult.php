<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 5/10/16
 * Time: 20:41
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
 * @ORM\Table(name="spoken_results")
 *
 * Class SpokenResult
 * @package App\Domain\Model\Evaluation
 */
class SpokenResult
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
     * @ORM\Column(type="text")
     *
     * @var string
     */
    protected $summary;

    public function __construct(Student $student)
    {
        $this->id = NtUid::generate(4);
        $this->student = $student;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $evaluation
     */
    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * @return mixed
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    /**
     * @return mixed
     */
    public function getStudent()
    {
        return $this->student;
    }

}