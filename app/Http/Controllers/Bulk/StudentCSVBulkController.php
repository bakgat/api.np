<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 14/09/16
 * Time: 08:30
 */

namespace App\Http\Controllers\Bulk;


use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Services\Identity\StudentService;
use App\Http\Controllers\Controller;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Cache;
use JMS\Serializer\SerializerInterface;

class StudentCSVBulkController extends Controller
{
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var StudentService */
    protected $studentService;

    public function __construct(StudentService $studentService, GroupRepository $groupReo, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->groupRepo = $groupReo;
        $this->studentService = $studentService;
    }

    public function bulkInsert()
    {
        $groups = $this->groupRepo->all();

        $collection = new ArrayCollection;
        $row = 0;
        if (($handle = fopen(resource_path() . "/students.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!$row++ == 0) {
                    $fn = $data[1];
                    $ln = $data[0];
                    $birthday = DateTime::createFromFormat('d.m.Y', $data[2]);
                    $gender = $data[3];
                    $schoolId = $data[4];

                    $groupName = $data[5];
                    $group = null;
                    if (Cache::has('group_by_name' . $groupName)) {
                        /** @var Group $group */
                        $group = Cache::get('group_by_name' . $groupName);
                    } else {
                        foreach ($groups as $g) {
                            if ($g->getName() == $groupName) {
                                $group = $g;
                                Cache::put('group_by_name' . $groupName, $g);
                                break;
                            }
                        }
                    }
                    $groupId = $group->getId();
                    $number = $data[6];

                    $insertData = [
                        'firstName' => $fn,
                        'lastName' => $ln,
                        'birthday' => $birthday->format('Y-m-d'),
                        'schoolId' => $schoolId,
                        'gender' => $gender,
                        'group' => [
                            'id' => $groupId
                        ],
                        'groupnumber' => $number
                    ];

                    $student = $this->studentService->create($insertData);
                    $collection->add($student);
                }
            }
            fclose($handle);
        }
        return $this->response($collection->toArray(), ['student_detail']);
    }
}