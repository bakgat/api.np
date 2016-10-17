<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/10/16
 * Time: 20:17
 */

namespace App\Domain\DTO;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;


use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
/**
 * @ORM\Entity
 * Class StudentDTO
 * @package App\Domain\DTO
 */
class StudentDTO implements \JsonSerializable
{
    /**
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     */
    protected $id;

    /**
     *
     * @ORM\Column(type="string")
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string")
     */
    protected $lastName;

    /**
     * @Groups({"result_dto"})
     * @ORM\OneToMany(targetEntity="StudentResultsDTO", mappedBy="student")
     * @var ArrayCollection
     */
    protected $results;

    /**
     * @Groups({"result_dto"})
     * @VirtualProperty
     *
     * @return string
     */
    public function getDisplayName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return ArrayCollection
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'displayName' => $this->getDisplayName(),
            'results' => $this->results
        ];
    }
}