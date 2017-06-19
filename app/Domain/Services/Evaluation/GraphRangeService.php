<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/17
 * Time: 09:38
 */

namespace App\Domain\Services\Evaluation;


use App\Domain\Model\Evaluation\GraphRange;
use App\Domain\Model\Evaluation\GraphRangeRepository;
use App\Domain\Model\Identity\GroupRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Cache;

class GraphRangeService
{
    /** @var GraphRangeRepository  */
    private $grRepo;
    /** @var GroupRepository  */
    private $groupRepo;

    public function __construct(GraphRangeRepository $graphRangeRepository, GroupRepository $groupRepository)
    {
        $this->grRepo = $graphRangeRepository;
        $this->groupRepo = $groupRepository;
    }

    public function all($group = null) {
        if($group) {
            $g = $this->groupRepo->get($group);
            if($g) {
                return $this->grRepo->all($g->getLevel());
            }
        }
        return $this->grRepo->all();
    }

    public function find(DateTime $date, $group = null) {
        $result = new ArrayCollection();
        /** @var GraphRange $range */
        foreach ($this->all($group) as $range) {
            if($range->contains($date)) {
                $result->add($range);
            }
        }
        return $result;
    }
}