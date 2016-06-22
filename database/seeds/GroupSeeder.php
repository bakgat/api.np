<?php
use App\Domain\Model\Identity\Group;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:30
 */
class GroupSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('groups')->delete();

        $faker = Faker\Factory::create('nl_BE');

        foreach (range(1, 20) as $index) {
            $w = $faker->unique()->word();

            $group = new Group($w);
            EntityManager::persist($group);
        }
        EntityManager::flush();
    }
}