<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/10/16
 * Time: 20:40
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
 * Class StudentResultDTO
 * @package App\Domain\DTO\Results
 */
class StudentResultDTO
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var  NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $firstName;

    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $group;

    /**
     * @Groups({"result_dto"})
     * @ORM\OneToMany(targetEntity="PointResultDTO", mappedBy="studentResult")
     * 
     * @var ArrayCollection
     */
    private $pointResults;

    /**
     * @Groups({"result_dto"})
     * @ORM\OneToMany(targetEntity="PointResultDTO", mappedBy="studentResult")
     *
     * @var ArrayCollection
     */
    //private $majorResults;


    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}