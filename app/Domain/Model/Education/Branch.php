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
use Webpatser\Uuid\Uuid;
use Doctrine\ORM\Mapping AS ORM;

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
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Major", inversedBy="branches")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Major
     */
    private $major;

    /**
     * @var BranchForGroup[]
     */
    private $branchForGroups;

    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;
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


}