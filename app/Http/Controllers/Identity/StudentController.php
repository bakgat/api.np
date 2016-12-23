<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/06/16
 * Time: 11:29
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use App\Domain\Services\Evaluation\IacService;
use App\Domain\Services\Identity\StudentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;
use JMS\Serializer\SerializerInterface;

class StudentController extends Controller
{
    /** @var StudentRepository studentRepo */
    private $studentRepo;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var StudentService */
    private $studentService;
    /** @var IacService */
    private $iacService;

    public function __construct(StudentRepository $studentRepo,
                                GroupRepository $groupRepository,
                                StudentService $studentService,
                                IacService $iacService,
                                SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->studentRepo = $studentRepo;
        $this->groupRepo = $groupRepository;
        $this->studentService = $studentService;
        $this->iacService = $iacService;
    }

    public function index(Request $request)
    {
        if ($request->has('flat')) {
            $field = $request->get('flat');
            $col = $this->studentRepo->flat($field);
            return $this->response($col);
        }
        if ($request->has('group')) {
            $groupId = NtUid::import($request->get('group'));
            $group = $this->groupRepo->get($groupId);
            return $this->response($this->studentRepo->allActiveInGroup($group), ['student_list']);
        }

        return $this->response($this->studentService->all(), ['student_list']);
    }

    public function show($id)
    {
        if (!$id instanceof NtUid) {
            $id = NtUid::import($id);
        }
        return $this->response($this->studentRepo->find($id), ['student_detail']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'schoolId' => 'required|unique:students,school_id',
            'gender' => 'required|in:M,F,O',
            'group.id' => 'required',
            'groupnumber' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $student = $this->studentService->create($data);
        return $this->response($student, ['student_detail']);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'schoolId' => 'required|unique:students,school_id,' . $request->get('id'),
            'gender' => 'required|in:M,F,O',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $student = $this->studentService->update($data);
        return $this->response($student, ['student_detail']);
    }

    /* ***************************************************
     * GROUPS
     * **************************************************/
    public function allGroups($id)
    {
        $student = $this->studentService->get($id);
        return $this->response($student->allStudentGroups(), ['student_groups']);
    }

    public function joinGroup(Request $request, $id)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $group = $request->get('group');
        $number = $request->get('number');

        $studentGroup = $this->studentService->joinGroup($id, $group['id'], $number, $start, $end);
        return $this->response($studentGroup, ['student_groups']);
    }

    public function updateGroup(Request $request, $studentGroupId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $number = $request->get('number');

        $studentGroup = $this->studentService->updateGroup($studentGroupId, $number, $start, $end);
        return $this->response($studentGroup, ['student_groups']);
    }

    /* ***************************************************
     * REDICODI
     * **************************************************/
    public function allRedicodi($id)
    {
        $student = $this->studentService->get($id);

        return $this->response($student->allStudentRedicodi(), ['student_redicodi']);
    }

    public function addRedicodi(Request $request, $id)
    {
        //TODO: validation !
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentRedicodi = $this->studentService->addRedicodi($id, $data);
        return $this->response($studentRedicodi, ['student_redicodi']);
    }

    public function updateRedicodi(Request $request, $studentRedicodiId)
    {
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentRedicodi = $this->studentService->updateRedicodi($studentRedicodiId, $data);
        return $this->response($studentRedicodi, ['student_redicodi']);
    }

    /* ***************************************************
     * IAC
     * **************************************************/
    public function allIac($id)
    {
        $iac = $this->iacService->getIACsForStudent($id, DateRange::infinite());
        return $this->response($iac, ['student_iac']);
    }

    public function addIac(Request $request, $id)
    {
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentIac = $this->iacService->addIac($id, $data);
        return $this->response($studentIac, ['student_iac']);
    }

    public function updateIac(Request $request, $iacId)
    {
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentIac = $this->iacService->updateIac($iacId, $data);
        return $this->response($studentIac, ['student_iac']);
    }

    public function destroyIac($iacId)
    {
        $this->iacService->deleteIAC($iacId);
        return $this->response(true);
    }


    public function makeAvatars()
    {
        $students = $this->studentService->all();

        $replace = [
            '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
            '&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
            '&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
            'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
            'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
            'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
            'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
            'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K',
            'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
            'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
            'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
            'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
            'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
            '&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
            'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
            'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
            'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
            'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
            'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
            'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
            'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
            'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
            'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
            'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
            'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
            'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
            '&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
            'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
            'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
            'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
            'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
            'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
            'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
            'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '',
            'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a',
            'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
            'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
            'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
            'ю' => 'yu', 'я' => 'ya'
        ];

        $prefix = 'http://KLIM:TOR@schkt.volglvs.be/PIX/';
        $suffix = '.JPG';

        /** @var Student $student */
        foreach ($students as $student) {
            $fn = str_replace(array_keys($replace), $replace, $student->getFirstName());
            $ln = str_replace(array_keys($replace), $replace, $student->getLastName());
            $bd = $student->getBirthday()->format('d.m.Y');
            $url = $prefix . str_replace(' ', '%20', $ln) . str_replace(' ', '%20', $fn) . $bd . $suffix;

            ini_set('user_agent', 'MSIE 4\.0b2;');

            $file_headers = @get_headers($url);
            if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                $exists = false;
            } else {
                $exists = true;
            }

            $imagePath = resource_path('avatars/' . $student->getId()->toString() . '.jpg');
            if ($exists) {
                copy($url, $imagePath);
            } else {
                copy(resource_path('avatars/_avatar_empty_2.png'), $imagePath);
            }
        }
    }

    public function getPic($id)
    {
        $img = resource_path('avatars/' . $id . '.jpg');
        return Image::make($img)->response();
    }

    public function postPic(Request $request, $id)
    {
        $request->file();
    }
}