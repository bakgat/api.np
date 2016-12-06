<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/12/16
 * Time: 21:24
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use JMS\Serializer\Annotation\Groups;

class McResult
{
    /**
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $settings;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $selected;

    public function __construct(NtUid $id, $settings, $selected)
    {
        $this->id = $id;
        $this->settings = $settings;
        $this->selected = $selected;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }


}