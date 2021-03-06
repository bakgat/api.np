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
use App\Domain\Model\Time\DateRangeTrait;
use DateTime;
use App\Domain\NtUid;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="branch_for_groups")
 *
 * Class BranchForGroup
 * @package App\Domain\Model\Education
 */
class BranchForGroup
{
    use DateRangeTrait;

    /**
     * @Groups({"evaluation_detail", "group_branches"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"group_evaluations", "evaluation_detail", "group_branches"})
     * @ORM\ManyToOne(targetEntity="Branch", inversedBy="branchForGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Branch
     */
    private $branch;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Model\Identity\Group", inversedBy="branchForGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Group
     */
    private $group;

    /**
     * @ORM\Column(type="evaluationtype")
     *
     * @var EvaluationType
     */
    private $evaluationType; //point - comprehensive - feedback

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $max;

    /**
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    private $dateRange;

    /**
     * @Groups({"evaluation_detail", "group_branches"})
     *
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $onlyFinal;

    public function __construct(Branch $branch, Group $group, $daterange, EvaluationType $evaluationType, $max = null, $onlyFinal = false)
    {
        $this->id = NtUid::generate(4);
        $this->branch = $branch;
        $this->group = $group;
        $this->evaluationType = $evaluationType;
        if ($this->evaluationType->getValue() === EvaluationType::POINT) {
            if ($max === null) {
                throw new MaxNullException();
            }
            $this->max = $max;
        }

        $this->dateRange = DateRange::fromData($daterange);
        $this->onlyFinal = false;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Branch
     */
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
        if ($this->evaluationType == EvaluationType::POINT) {
            $this->max = $max;
        }
        return $this;
    }

    public function isOnlyFinal() {
        return $this->onlyFinal;
    }

    /**
     * Stops the relation between a branch and a group at a certain date
     *
     * @param DateTime|null $end
     * @return BranchForGroup
     */
    public function leaveGroup($end = null)
    {

        //group is already left
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
