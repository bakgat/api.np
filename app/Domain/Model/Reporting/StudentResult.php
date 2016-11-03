<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:59
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;


use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

class StudentResult
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
    private $firstName;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $lastName;

    public function __construct(NtUid $id, $firstName, $lastName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }
    
}