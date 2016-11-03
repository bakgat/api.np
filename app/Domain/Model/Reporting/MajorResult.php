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
     * @Groups({"result_dto"})
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $name;

    /**
     * @var ArrayCollection
     */
    private $branches;

    /**
     * @var NtUid[]
     */
    private $branchIds;

    public function __construct(NtUid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->branches = new ArrayCollection;
        $this->branchIds = [];
    }

    public function getId()
    {
        return $this->id;
    }
}