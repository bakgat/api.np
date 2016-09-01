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

        $groups = ['K1A', 'K1KB', 'K1C', 'K2A', 'K2B', 'K3A', 'K3B',
            'L1A', 'L1B', 'L1C', 'L2A', 'L2B', 'L3A', 'L3B', 'L4A', 'L4B', 'L5A', 'L5B', 'L6A', 'L6B'];

        foreach ($groups as $group) {
            $group = new Group($group, true);
            EntityManager::persist($group);
        }

        EntityManager::flush();
        EntityManager::clear();
    }
}