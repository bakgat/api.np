<?php
use App\Domain\Model\Person\Student;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:30
 */
class StudentSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->delete();

        $qb = EntityManager::createQueryBuilder();
        $qb->select('g')
            ->from('App\Domain\Model\Group\Group', 'g');

        $groups = $qb->getQuery()->getResult();

        $faker = Faker\Factory::create('nl_BE');
        foreach (range(1, 440) as $index) {
            $student = new Student(
                $faker->firstName(),
                $faker->lastName(),
                $faker->email()
            );
            $student->setBirthday($faker->dateTimeBetween('-12years', '-3years'));
            $student->joinGroup($faker->randomElement($groups));
            EntityManager::persist($student);
        }
        EntityManager::flush();
    }
}