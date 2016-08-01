<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 15:58
 */

namespace App\Domain\Model\Evaluation;

use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Time\DateRange;
use App\Domain\Model\Time\DateRangeTrait;
use App\Domain\Uuid;
use \DateTime;
use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\Groups;


/**
 * @ORM\Entity
 * @ORM\Table(name="redicodi_for_students")
 *
 * Class RedicodiForStudent
 * @package App\Domain\Model\Evaluation
 */
class RedicodiForStudent
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
     * @Groups({})
     *
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Student", inversedBy="redicodiForStudents")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @Groups({"student_redicodi"})
     * @ORM\Column(type="redicoditype")
     *
     * @var Redicodi
     */
    protected $redicodi;

    /**
     * @Groups({"student_redicodi"})
     *
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Education\Branch")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Branch
     */
    protected $branch;

    /**
     * @Groups({"student_redicodi"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $content;
    /**
     * @Groups({"student_redicodi"})
     *
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    protected $dateRange;

    public function __construct(Student $student, Redicodi $redicodi, Branch $branch, $content = '', $dateRange)
    {
        $this->id = Uuid::generate(4);
        $this->student = $student;
        $this->redicodi = $redicodi;
        $this->branch = $branch;
        $this->content = $content;

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

    public function getRedicodi()
    {
        return $this->redicodi;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Stops the redicodi for a given student and branch at a certain date
     *
     * @param DateTime|null $end
     * @return RedicodiForStudent
     */
    public function stopRedicodi($end = null)
    {
        //redicodi already stopped
        if (!$this->isActive()) {
            return $this;
        }

        if ($end == null) {
            $now = new DateTime;
            $end = $now->modify('-1 day');
        }

        $dr = ['start' => $this->dateRange->getStart(), 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);

        return $this;
    }
}