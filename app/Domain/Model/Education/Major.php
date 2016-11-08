<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/06/16
 * Time: 15:58
 */

namespace App\Domain\Model\Education;


use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\NtUid;
use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="majors")
 *
 * Class Major
 * @package App\Domain\Model\Education
 */
class Major
{
    /**
     * @Groups({"group_branches", "major_list", "branch_list", "student_list", "student_redicodi", "group_evaluations",
     *     "evaluation_detail", "iac_goals", "student_iac"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"group_branches", "major_list", "branch_list", "student_list", "student_redicodi", "group_evaluations",
     *     "evaluation_detail", "iac_goals", "student_iac"})
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    private $name;

    /**
     * @Groups({"major_list", "iac_goals"})
     * @ORM\OneToMany(targetEntity="Branch", mappedBy="major", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    private $branches;

    public function __construct($name)
    {
        $this->id = NtUid::generate(4);
        $this->name = $name;
        $this->branches = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }


    public function changeName($name)
    {
        $this->name = $name;
    }

    public function addBranch(Branch $branch)
    {
        $this->branches->add($branch);
        $branch->joinMajor($this);
        return $this;
    }


    public function getBranches()
    {
        return $this->branches->toArray();
    }

}