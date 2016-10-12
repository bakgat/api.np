<?php

use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\NtUid;
use Illuminate\Database\Seeder;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/07/16
 * Time: 22:07
 */
class SpokenSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $qb = EntityManager::createQueryBuilder();
        $qb->select('bfg, b, g')
            ->from('App\Domain\Model\Education\BranchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('bfg.group', 'g')
            ->where('bfg.evaluationType=:type')
            ->setParameter('type', 'C');

        $bfgs = $qb->getQuery()->getResult();

        /** @var \App\Domain\Model\Education\BranchForGroup $bfg */
        foreach ($bfgs as $bfg) {
            $branch = $bfg->getBranch();
            $branch->joinGroup($bfg->getGroup(), new EvaluationType(EvaluationType::MULTIPLECHOICE), null, new DateTime);
            EntityManager::persist($branch);
        }

        EntityManager::flush();
        EntityManager::clear();
    }
}