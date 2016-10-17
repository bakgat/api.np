<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/10/16
 * Time: 13:43
 */

namespace App\Domain\DTO;

use App\Domain\NtUid;
use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 *
 * Class StudentResultsDTO
 * @package App\Domain\DTO
 */
class StudentResultsDTO
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     */
    protected $id;

    /**
     * @Groups({"result_dto"})
     * @ORM\ManyToOne(targetEntity="StudentDTO", inversedBy="results")
     * @var StudentDTO
     */
    protected $student;
    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="string")
     */
    public $branch;
    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="boolean")
     */
    public $permanent;
    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="integer")
     */
    public $max;
    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="float")
     */
    public $result;


}