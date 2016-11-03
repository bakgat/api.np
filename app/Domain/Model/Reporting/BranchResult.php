<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 10:52
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
class BranchResult
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
    private $graphRanges;

    public function __construct(NtUid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->graphRanges = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }
}