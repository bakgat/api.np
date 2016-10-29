<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/10/16
 * Time: 20:42
 */

namespace App\Domain\DTO\Results;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity
 *
 * Class BranchResultsDTO
 * @package App\Domain\DTO\Results
 */
class BranchResultsDTO
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $name;

    
    public function __construct()
    {
        $this->pointResults = new ArrayCollection;
    }
}