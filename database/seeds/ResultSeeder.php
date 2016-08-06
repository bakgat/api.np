<?php

use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\StudentInGroup;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 4/08/16
 * Time: 16:40
 */
class ResultSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $qb = EntityManager::createQueryBuilder();
        $qb->select('g, bfg, b')
            ->from('App\Domain\Model\Identity\Group', 'g')
            ->join('g.branchForGroups', 'bfg')
            ->join('bfg.branch', 'b');

        /** @var Group[] $groups */
        $groups = $qb->getQuery()->getResult();

        $faker = Faker\Factory::create('nl_BE');

        foreach ($groups as $group) {
            foreach ($group->getBranchForGroups() as $branchForGroup) {
                $evCount = $faker->biasedNumberBetween(1, 5);
                foreach (range(1, $evCount) as $index) {
                    $title = $faker->realText(20);
                    $max = null;
                    $date = $faker->dateTimeBetween('-1year');
                    $permanent = $index != $evCount;

                    if ($branchForGroup->getEvaluationType()->getValue() == 'P') {
                        $max = $faker->randomElement([10, 20]);
                    }
                    $ev = new Evaluation($branchForGroup, $title, $date, $max, $permanent);

                    if ($branchForGroup->getEvaluationType()->getValue() == 'P') {
                        foreach ($group->getStudentInGroups() as $studentInGroup) {
                            if ($studentInGroup->isActive()) {
                                $student = $studentInGroup->getStudent();
                                $score = $faker->biasedNumberBetween(0, $ev->getMax());
                                $pr = new PointResult($student, $score);

                                $ev->addResult($pr);
                            }
                        }
                    }

                    EntityManager::persist($ev);
                    EntityManager::flush();
                }


            }
        }

    }
}