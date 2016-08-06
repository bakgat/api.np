<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('GroupSeeder');
        $this->call('EducationSeeder');
        $this->call('PersonSeeder');
        $this->call('RedicodiSeeder');
        $this->call('ResultSeeder');;

    }

}
