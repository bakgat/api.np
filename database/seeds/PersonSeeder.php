<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffType;
use App\Domain\Model\Identity\Student;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:30
 */
class PersonSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->delete();
        DB::table('staff')->delete();

        $qb = EntityManager::createQueryBuilder();
        $qb->select('g')
            ->from('App\Domain\Model\Identity\Group', 'g')
            ->where('g.active=?1')
            ->setParameter(1, true); //only use active groups

        $groups = $qb->getQuery()->getResult();

        $qbR = EntityManager::createQueryBuilder();
        $qbR->select('r')
            ->from('App\Domain\Model\Identity\Role', 'r');

        $roles = $qbR->getQuery()->getResult();

        $faker = Faker\Factory::create('nl_BE');
        foreach (range(1, 440) as $index) {
            /** @var Student $student */
            $student = new Student(
                $faker->firstName(),
                $faker->lastName(),
                $faker->unique()->creditCardNumber(),
                new Gender($faker->randomElement(['M', 'F'])),
                $faker->dateTimeBetween('-12years', '-3years')
            );
            $student->joinGroup($faker->unique(true)->randomElement($groups));
            for ($i = 0; $i < $faker->biasedNumberBetween(1, 10); $i++) {
                $lower = $faker->dateTimeBetween('-9years', '-1year');
                $upper = $faker->dateTimeBetween($lower, 'now');
                $student->joinGroup($faker->unique()->randomElement($groups), null, $lower, $upper);
            }

            EntityManager::persist($student);
        }


        foreach (range(1, 40) as $index) {
            $staff = new Staff(
                $faker->firstName,
                $faker->lastName,
                $faker->email(),
                new Gender($faker->randomElement(['M', 'F'])),
                $faker->dateTimeBetween('-60years', '-21years')
            );
            $staff->joinGroup($faker->unique(true)->randomElement($groups), new StaffType(StaffType::TEACHER));
            $staff->assignRole($faker->randomElement($roles));
            EntityManager::persist($staff);
        }

        EntityManager::flush();
        EntityManager::clear();
    }
}