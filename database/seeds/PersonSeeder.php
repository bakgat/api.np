<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\RoleRepository;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffType;
use App\Domain\Model\Identity\Student;
use App\Domain\Services\Identity\StaffService;
use App\Domain\Services\Identity\StudentService;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:30
 */
class PersonSeeder extends Seeder
{
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var RoleRepository */
    protected $roleRepo;
    /** @var StudentService */
    protected $studentService;
    /** @var StaffService */
    protected $staffService;

    /**
     * PersonSeeder constructor.
     * @param GroupRepository $groupRepository
     * @param StudentService $studentService
     */
    public function __construct(GroupRepository $groupRepository,
                                RoleRepository $roleRepository,
                                StudentService $studentService,
                                StaffService $staffService)
    {
        $this->groupRepo = $groupRepository;
        $this->roleRepo = $roleRepository;
        $this->studentService = $studentService;
        $this->staffService = $staffService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->delete();
        DB::table('staff')->delete();

        /*$qb = EntityManager::createQueryBuilder();
        $qb->select('g')
            ->from('App\Domain\Model\Identity\Group', 'g')
            ->where('g.active=?1')
            ->setParameter(1, true); //only use active groups

        $groups = $qb->getQuery()->getResult();*/

        $groups = $this->groupRepo->all();

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

                    $this->studentService->create($insertData);
                }
            }
            fclose($handle);
        }

        /* ***************************************************
         * Staff
         * **************************************************/

        $roles = $this->roleRepo->all();

        $row = 0;
        if (($handle = fopen(resource_path() . "/staff.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!$row++ == 0) {
                    $fn = $data[1];
                    $ln = $data[0];
                    $birthday = DateTime::createFromFormat('d.m.Y', $data[2]);
                    $gender = $data[3];
                    $email = $data[5];

                    $insertData = [
                        'firstName' => $fn,
                        'lastName' => $ln,
                        'birthday' => $birthday->format('Y-m-d'),
                        'email' => $email,
                        'gender' => $gender
                    ];

                    $staff = $this->staffService->create($insertData);
                    switch($staff->getDisplayName()) {
                        case 'Karl Van Iseghem':
                            $roleName = 'SUPERADMIN';
                            break;
                        case 'Rebekka Buyse':
                            $roleName = 'MANAGER';
                            break;
                        case 'Ann Helsmoortel':
                            $roleName = 'SECRETARY';
                            break;
                        default:
                            $roleName = 'TEACHER';
                            break;
                    }

                    $role = null;
                    if (Cache::has('role_by_name' . $roleName)) {

                        $role = Cache::get('role_by_name' . $roleName);
                    } else {
                        foreach ($roles as $r) {
                            if ($r->getName() == $roleName) {
                                $role = $r;
                                Cache::put('role_by_name' . $roleName, $r);
                                break;
                            }
                        }
                    }

                    $staff->assignRole($role);
                    EntityManager::persist($staff);

                }
            }
            fclose($handle);
        }


        EntityManager::flush();
        EntityManager::clear();
    }
}


/*
 * $groups = $this->groupRepo->all();

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
 */