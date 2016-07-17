<?php

use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\EvaluationType;
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

        $majors = [];
        foreach (range(1, 10) as $index) {
            $major = new Major($faker->unique()->word());

            $majors[] = $major;

            foreach (range(1, 10) as $index) {
                $branch = new Branch($faker->unique(true)->word());
                $major->addBranch($branch);
                $branch->joinMajor($major);
                $evType = $faker->randomElement($evaluationTypes);
                $max = $evType->getValue() === EvaluationType::POINT ? $faker->biasedNumberBetween(0, 100) : null;
                $lower = $faker->dateTimeBetween('-9years', '-1year');

                $branch->joinGroup($faker->unique()->randomElement($groups), $evType, $max, $lower);
            }
            EntityManager::persist($major);
        }



        EntityManager::flush();
    }
}