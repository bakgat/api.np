<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/08/16
 * Time: 19:52
 */

namespace App\Domain\Model\Education;


use App\Domain\NtUid;

use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;
/**
 * @ORM\Entity
 * @ORM\Table(name="goals")
 *
 * Class Goal
 * @package App\Domain\Model\Education
 */
class Goal
{
    /**
     * @Groups({"iac_goals", "student_iac"})
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Branch", inversedBy="goals")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Branch
     */
    protected $branch;

    /**
     * @Groups({"iac_goals", "student_iac"})
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $text;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $order;

    public function __construct(Branch $branch, $text)
    {
        $this->id = NtUid::generate(4);
        $this->branch = $branch;
        $this->text = $text;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getText()
    {
        return $this->text;
    }

    public function switchBranch(Branch $branch)
    {
        $this->branch = $branch;
    }

    public function updateText($text)
    {
        $this->text = $text;
    }
}