<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/06/16
 * Time: 15:58
 */

namespace App\Domain\Model\Education;


use Doctrine\Common\Collections\ArrayCollection;
use Webpatser\Uuid\Uuid;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="majors")
 *
 * Class Major
 * @package App\Domain\Model\Education
 */
class Major implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Branch", mappedBy="major", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    private $branches;

    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;
        $this->branches = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }


    public function changeName($name)
    {
        $this->name = $name;
    }

    public function addBranch(Branch $branch)
    {
        $this->branches->add($branch);
        $branch->joinMajor($this);
        return $this;
    }

    public function removeBranch($id)
    {
        $this->branches->remove($id);
        return $this;
    }


    public function getBranches()
    {
        return $this->branches->toArray();
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
            'branches' => $this->getBranches(),
        ];
    }
}