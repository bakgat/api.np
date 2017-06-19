<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/17
 * Time: 09:33
 */

namespace App\Domain\Model\Evaluation;


use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

interface GraphRangeRepository
{
    /**
     * @return ArrayCollection | GraphRange[]
     */
    public function all($group = null);
}