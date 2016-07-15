<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/07/16
 * Time: 09:17
 */

namespace App\Domain\Model\Education;


use App\Domain\Model\Education\Exceptions\MaxNullException;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Time\DateRange;
use App\Domain\Model\Identity\Group;
use DateTime;
use Webpatser\Uuid\Uuid;

class BranchForGroup
{
    /**
     * @var Uuid
     */
    private $id;

    /**
     * @var Branch
     */
    private $branch;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var EvaluationType
     */
    private $evaluationType; //point - comprehensive

    /**
     * @var int|null
     */
    private $max;

    /**
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    private $daterange;

    public function __construct(Branch $branch, Group $group, EvaluationType $evaluationType, $max = null)
    {
        $this->id = Uuid::generate(4);
        $this->branch = $branch;
        $this->group = $group;
        $this->evaluationType = $evaluationType;
        if ($this->evaluationType = EvaluationType::POINT) {
            if ($max === null) {
                throw new MaxNullException();
            }
            $this->max = $max;
        }
        $now = new DateTime;
        $this->daterange = DateRange::fromData(['start'=>$now->format('Y-m-d')]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getEvaluationType()
    {
        return $this->evaluationType;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function changeMax($max)
    {
        if ($this->evaluationType === EvaluationType::POINT) {
            $this->max = $max;
        }
        return $this;
    }

}
