<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 10:43
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;


use JMS\Serializer\Annotation\Groups;
class MajorResult
{
    /**
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $name;

    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $branches;

    public function __construct(NtUid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->branches = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function intoBranch($data)
    {
        $id = NtUid::import($data['bId']);
        $branch = $this->hasBranch($id);
        if (!$branch) {
            $name =  $data['bName'];
            $branch = new BranchResult($id, $name);
            $this->branches->add($branch);
        }
        return $branch;
    }

    public function hasBranch(NtUid $id)
    {
        $branch = $this->branches->filter(function ($element) use ($id) {
            /** @var MajorResult $element */
            return $element->getId() == $id;
        })->first();
        return $branch;
    }

    public function getBranchResults()
    {
        return clone $this->branches;
    }
}