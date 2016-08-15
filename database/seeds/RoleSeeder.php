<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 16:12
 */
class RoleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();

        $r_admin = new Role('SUPERADMIN');
        $r_manager = new Role('MANAGER');
        $r_teacher = new Role('TEACHER');
        $r_secretary = new Role('SECRETARY');
        $r_caremanager = new Role('CAREMANAGER');

        EntityManager::persist($r_admin);
        EntityManager::persist($r_manager);
        EntityManager::persist($r_teacher);
        EntityManager::persist($r_secretary);
        EntityManager::persist($r_caremanager);

        $karl = new Staff(
            'Karl',
            'Van Iseghem',
            'karl.vaniseghem@klimtoren.be',
            new Gender(Gender::MALE),
            new DateTime()
        );
        $karl->assignRole($r_admin);
        $karl->assignRole($r_caremanager);

        EntityManager::persist($karl);
        EntityManager::flush();
        EntityManager::clear();
    }
}