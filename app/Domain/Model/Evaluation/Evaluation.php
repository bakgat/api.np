<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/08/16
 * Time: 09:57
 */

namespace App\Domain\Model\Evaluation;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Uuid;
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
     * @Groups({"group_evaluations"})
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;

    /**
     * @Groups({"group_evaluations"})
     *
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Education\BranchForGroup")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var BranchForGroup
     */
    protected $branchForGroup;


    /**
     * @Groups({"group_evaluations"})
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date")
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @Groups({"group_evaluations"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $title;

    /**
     * @Groups({"group_evaluations"})
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $permanent; //permanent or end

    /**
     * @Groups({"group_evaluations"})
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $max;



    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Evaluation\PointResult", mappedBy="evaluation", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $results;


    public function __construct(BranchForGroup $branchForGroup, $title, $date = null, $max = null, $permanent = true)
    {
        $this->id = Uuid::generate(4);
        $this->branchForGroup = $branchForGroup;
        $this->title = $title;
        $this->permanent = $permanent;
        $this->max = $max;
        if ($date == null) {
            $date = new DateTime;
        }
        $this->date = $date;
        $this->results = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBranch()
    {
        return $this->branchForGroup->getBranch();
    }

    public function getGroup()
    {
        return $this->branchForGroup->getGroup();
    }

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

    public function getMax()
    {
        return $this->max;
    }

    /**
     * @VirtualProperty
     * @Groups({"group_evaluations"})
     *
     * @return float
     */
    public function getAverage()
    {
        return collection_average($this->results, 'score');
    }

    /**
     * @VirtualProperty
     * @Groups({"group_evaluations"})
     *
     * @return float
     */
    public function getMedian()
    {
        return collection_median($this->results, 'score');
    }

    public function getResults() {
        $results = [];
        foreach ($this->results as $result) {
            $results[] = $result;
        }
        return $results;
    }
    public function addResult(PointResult $result)
    {
        $this->results->add($result);
        $result->setEvaluation($this);
    }
}