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

class Major
{
    /** @var Uuid */
    private $id;

    /** @var string */
    private $name;

    /** @var ArrayCollection */
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
        return $this;
    }

    public function removeBranch($id)
    {
        $this->branches->remove($id);
        return $this;
    }
}