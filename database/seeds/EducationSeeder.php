<?php

use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Uuid;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/07/16
 * Time: 22:07
 */
class EducationSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('branches')->delete();
        DB::table('majors')->delete();

        $qb = EntityManager::createQueryBuilder();
        $qb->select('g')
            ->from('App\Domain\Model\Identity\Group', 'g');

        $groups = $qb->getQuery()->getResult();
        $evaluationTypes = EvaluationType::values();

        $faker = Faker\Factory::create('nl_BE');

        $majors = [
            'wiskunde' => [
                'getallenkennis',
                'hoofdrekenen',
                'cijferen',
                'meten en metend rekenen',
                'meetkunde',
                'toepassingen'
            ],
            'Nederlands' => [
                'taalbeschouwing',
                'spelling',
                'lezen',
                'spreken',
                'schrijven'

            ],
            'wereldoriëntatie' => [
                'kennis',
                'vaardigheden & attitudes'
            ], 'godsdienst' => [
                'kennis',
                'vaardigheden & attitudes'
            ], 'Frans' => [
                'schrijven',
                'spreken',
                'communicatieve vaardigheden'
            ],
            'muzische vorming' => [
                'muzikale opvoeding',
                'lichamelijke opvoeding',
                'bewegingsexpressie',
                'dramatische expressie'
            ]];
        foreach ($majors as $key => $value) {
            $major = new Major($key);

            foreach ($value as $b) {
                $branch = new Branch($b);
                $major->addBranch($branch);
                $branches[] = $branch;
            }

            EntityManager::persist($major);
        }

        foreach ($groups as $group) {

            /** @var Branch $branch */
            foreach ($branches as $branch) {
                $major = $branch->getMajor();
                $evType = null;
                if($major->getName()=='wiskunde'||$major->getName()=='Nederlands'||$branch->getName()=='kennis'||$major->getName()=='Frans') {
                    $max = $faker->biasedNumberBetween(0, 100);
                    $evType = new EvaluationType(EvaluationType::POINT);
                } else {
                    $evType = new EvaluationType(EvaluationType::COMPREHENSIVE);
                }
                $lower = new DateTime;

                $branch->joinGroup($group, $evType, $max, $lower);
                EntityManager::persist($branch);
            }
        }

        EntityManager::flush();
        EntityManager::clear();
    }
}