<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/07/16
 * Time: 09:17
 */

namespace App\Domain\Model\Education;


use App\Domain\Model\Time\DateRange;
use DoctrineProxies\__CG__\App\Domain\Model\Identity\Group;
use Webpatser\Uuid\Uuid;

class BranchForGroup
{
    private $id;

    private $branch;

    private $group;

    private $evaluationType; //point - comprehensive

    private $max;

    /**
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    private $daterange;

    public function __construct(Branch $branch, Group $group, $evaluationType, $max = null)
    {
        $this->id = Uuid::generate(4);
        $this->branch = $branch;
        $this->group = $group;
        $this->evaluationType = $evaluationType;
        $this->max = $max;
        $this->daterange = new DateRange(new DateTime, null);
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

}
