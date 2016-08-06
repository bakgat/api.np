<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Identity\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 21:42
 */
class RedicodiSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('redicodi_for_students')->delete();

        $qb = EntityManager::createQueryBuilder();
        $qb->select('s')
            ->from(Student::class, 's');

        $students = $qb->getQuery()->getResult();

        $qbBranches = EntityManager::createQueryBuilder();
        $qbBranches->select('b')
            ->from(Branch::class, 'b');
        $branches = $qbBranches->getQuery()->getResult();

        $redicodiTypes = Redicodi::values();

        $faker = Faker\Factory::create('nl_BE');

        foreach (range(1, 50) as $index) {
            $student = $faker->randomElement($students);
            $redicodi = $faker->randomElement($redicodiTypes);
            $branch = $faker->randomElement($branches);
            $content = $faker->text(50);

            $lower = $faker->dateTimeBetween('-9years', '-1day');

            $rfs = new RedicodiForStudent($student, $redicodi, $branch, $content, ['start' => $lower]);
            EntityManager::persist($rfs);
        }

        EntityManager::flush();
        EntityManager::clear();
    }
}