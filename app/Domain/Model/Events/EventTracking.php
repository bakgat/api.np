<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/09/16
 * Time: 08:17
 */

namespace App\Domain\Model\Events;


use App\Domain\NtUid;
use DateTime;
use Doctrine\ORM\Mapping AS ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="event_tracking")
 *
 * Class EventTracking
 * @package App\Domain\Model\Events
 */
class EventTracking
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $userTable;

    /**
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $userId;

    /**
     * @ORM\Column(type="guid", nullable=true)
     *
     * @var string
     */
    protected $actionTable;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $action;

    /**
     * @ORM\Column(type="guid", nullable=true)
     *
     * @var NtUid
     */
    protected $actionId;


    public function __construct($userTable, $userId, $actionTable, $action, $actionId)
    {
        $this->id = NtUid::generate(4);
        $this->userTable = $userTable;
        $this->userId = $userId;
        $this->actionTable = $actionTable;
        $this->action = $action;
        $this->actionId = $actionId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserTable()
    {
        return $this->userTable;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getActionTable()
    {
        return $this->actionTable;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getActionId()
    {
        return $this->actionId;
    }

}