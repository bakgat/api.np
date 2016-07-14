<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:35
 */

namespace App\Domain\Model\Identity;


use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Contracts\Support\Arrayable;
use Webpatser\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="groups")
 *
 * Class Group
 * @package App\Domain\Model\Identity
 */
class Group implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid id
     */
    protected $id;
    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="group", cascade={"persist"})
     *
     * @var StudentInGroups[]
     */
    protected $studentInGroups;


    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;

    }

    public function getId()
    {
        if ($this->id instanceof Uuid) {
            return $this->id;
        }
        return Uuid::import($this->id);
    }

    public function getName()
    {
        return $this->name;
    }

    public function updateName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    public function toString()
    {
        return $this->__toString();
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id' => (string)$this->getId(),
            'name' => $this->getName(),
        ];
    }
}