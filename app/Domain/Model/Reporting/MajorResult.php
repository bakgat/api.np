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
     * @Groups({"result_dto", "student_iac"})
     * @var string
     */
    private $name;

    /**
     * @Groups({"result_dto", "student_iac"})
     * @var ArrayCollection
     */
    private $branches;
    /**
     * @var int
     */
    private $order;

    public function __construct(NtUid $id, $name, $order = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->order = $order;
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
            $name = $data['bName'];
            $order = $data['bOrder'];
            $branch = new BranchResult($id, $name, $order);
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

    public function getOrder()
    {
        return $this->order;
    }

    public function sort()
    {

        $iterator = $this->branches->getIterator();
        /**
         * @var BranchResult $a
         * @var BranchResult $b
         */
        $iterator->uasort(function ($a, $b) {
            /**
             * @var BranchResult $a
             * @var BranchResult $b
             */
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });
        $this->branches = new ArrayCollection(iterator_to_array($iterator));
        return $this->branches;
    }
}