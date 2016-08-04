<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/08/16
 * Time: 09:57
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Uuid;
use App\Support\BrilliantArrayCollection;
use DateTime;

class Evaluation
{
    /**
     * @var Uuid
     */
    protected $id;

    /**
     * @var BranchForGroup
     */
    protected $branchForGroup;


    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $permanent; //permanent or end

    /**
     * @var int
     */
    protected $max;

    /**
     * @var float
     */
    protected $average;

    /**
     * @var float
     */
    protected $median;

    /**
     * @var BrilliantArrayCollection
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
        $this->results = new BrilliantArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBranch()
    {
        return $this->branchForGroup->getBranch();
    }
    public function getGroup() {
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

    public function getAverage()
    {
        return $this->results->average('score');
    }

    public function getMedian()
    {
        return $this->results->median('score');
    }

    public function addResult(PointResult $result)
    {
        $this->results->add($result);
        $result->setEvaluation($this);
    }
}