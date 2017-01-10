<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/06/16
 * Time: 16:01
 */

namespace App\Domain\Model\Education;


use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Identity\Group;
use DateTime;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="branches")
 *
 * Class Branch
 * @package App\Domain\Model\Education
 */
class Branch
{
    /**
     * @Groups({"group_branches", "major_list", "branch_list", "student_list", "student_redicodi",
     *     "group_evaluations", "evaluation_detail", "iac_goals", "student_iac"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"group_branches", "major_list", "branch_list", "student_list", "student_redicodi",
     *     "group_evaluations", "evaluation_detail", "iac_goals", "student_iac"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @Groups({"group_branches", "major_list", "branch_list", "student_list", "student_redicodi",
     *     "group_evaluations", "evaluation_detail", "iac_goals", "student_iac"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $shortName;

    /**
     * @Groups({"group_branches", "branch_list", "student_redicodi", "group_evaluations", "evaluation_detail", "student_iac"})
     *
     * @ORM\ManyToOne(targetEntity="Major", inversedBy="branches")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Major
     */
    private $major;

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Education\BranchForGroup", mappedBy="branch", cascade={"persist"})
     *
     * @var BranchForGroup[]
     */
    private $branchForGroups;

    /**
     * @Groups({"iac_goals"})
     * @ORM\OneToMany(targetEntity="Goal", mappedBy="branch", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    private $goals;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $order;

    public function __construct($name)
    {
        $this->id = NtUid::generate(4);
        $this->name = $name;
        $this->goals = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function joinMajor(Major $major)
    {
        $this->major = $major;
    }

    public function getMajor()
    {
        return $this->major;
    }

    public function changeName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    public function joinGroup(Group $group, EvaluationType $evaluationType, $max = null, $start = null, $end = null)
    {
        if ($start == null) {
            $start = new DateTime;
        }
        $branchForGroup = new BranchForGroup($this, $group, ['start' => $start, 'end' => $end], $evaluationType, $max);
        $this->branchForGroups[] = $branchForGroup;

        return $this;
    }

    public function leaveGroup(Group $group, $evaluationType = null, $end = null)
    {
        $id = $group->getId();
        foreach ($this->branchForGroups as $branchForGroup) {

            if ($evaluationType == null || $branchForGroup->getEvaluationType()->getValue() == $evaluationType->getValue()) {
                if ($branchForGroup->getGroup()->getId() == $id) {
                    $branchForGroup->leaveGroup($end);
                }
            }
        }
    }

    /**
     * @param EvaluationType $evaluationType
     * @return \App\Domain\Model\Identity\Group[]
     */
    public function getGroups(EvaluationType $evaluationType = null)
    {
        $groups = [];
        foreach ($this->branchForGroups as $branchForGroup) {
            if ($evaluationType == null || $branchForGroup->getEvaluationType() == $evaluationType) {
                $groups[] = $branchForGroup->getGroup();
            }
        }
        return $groups;
    }

    /**
     * @param EvaluationType $evaluationType
     * @return \App\Domain\Model\Identity\Group[]
     */
    public function getActiveGroups(EvaluationType $evaluationType = null)
    {
        $groups = [];
        foreach ($this->branchForGroups as $branchForGroup) {
            if ($branchForGroup->isActive()) {
                if ($evaluationType == null || $branchForGroup->getEvaluationType() == $evaluationType) {
                    $groups[] = $branchForGroup->getGroup();
                }
            }
        }
        return $groups;
    }

    /**
     * @return ArrayCollection
     */
    public function getGoals()
    {
        return clone $this->goals;
    }
}