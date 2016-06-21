<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:35
 */

namespace App\Domain\Model\Identity;


use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JsonSerializable;
use Webpatser\Uuid\Uuid;

/**
 *
 * Class Group
 * @package App\Domain\Model\Identity
 */
class Group
{
    /** @var Uuid id */
    protected $id;
    protected $name;


    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;

    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

}