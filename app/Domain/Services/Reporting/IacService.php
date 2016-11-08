<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 20:34
 */

namespace App\Domain\Services\Reporting;


use App\Domain\Model\Evaluation\IACRepository;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;

class IacService
{
    /** @var IACRepository */
    private $iacRepo;
    /** @var StudentRepository */
    private $studentRepo;

    public function __construct(IACRepository $iacRepository, StudentRepository $studentRepository)
    {
        $this->iacRepo = $iacRepository;
        $this->studentRepo = $studentRepository;
    }

    public function getIACsForStudent($studentId, DateRange $range)
    {
        $id = NtUid::import($studentId);
        $student = $this->studentRepo->get($id);
        $data = $this->iacRepo->iacForStudent($student);
        return $this->generateIac($data, $range);
    }

    private function generateIac($data, DateRange $range)
    {
        $iac = new Report($range);
        foreach ($data as $item) {
            $iac->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoIac($item)
                ->intoGoal($item);
        }

        return $iac;
    }


}