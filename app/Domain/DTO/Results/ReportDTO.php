<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/10/16
 * Time: 20:37
 */

namespace APP\Domain\DTO\Results;

use App\Domain\Model\Time\DateRange;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ReportDTO
 * @package APP\Domain\DTO\Results
 */
class ReportDTO
{
    /**
     * @var DateRange
     */
    private $range;

    /**
     * @var ArrayCollection
     */
    private $studentResults;

    public function __construct($range)
    {
        $this->range = DateRange::fromData($range);
        $this->studentResults = new ArrayCollection;
    }

    /**
     * @param ArrayCollection $studentResults
     */
    public function addAllStudentResults(ArrayCollection $studentResults)
    {
        foreach ($studentResults as $studentResult) {
            $this->studentResults->add($studentResult);
        }
    }
}