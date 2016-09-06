<?php
use App\Domain\Model\Identity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/09/16
 * Time: 16:40
 */
class RoleControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $roleRepo;

    public function setUp()
    {
        parent::setUp();
        $this->roleRepo = $this->mock(App\Domain\Model\Identity\RoleRepository::class);
    }

    /**
     * @test
     * @group rolerepo
     */
    public function should_serialize_index()
    {
        $this->roleRepo->shouldReceive('all')
            ->once()
            ->andReturn($this->makeRoleCollection());

        $this->get('/roles')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'name'
                ]
            ]);
    }


    /* ***************************************************
     * PRIVATE METHODS
     * **************************************************/
    /**
     * @return Role
     */
    private function makeRole()
    {
        $role = new Role($this->faker->unique()->word);
        return $role;
    }

    /**
     * @return ArrayCollection
     */
    private function makeRoleCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 5) as $item) {
            $role = $this->makeRole();
            $collection->add($role);
        }
        return $collection;
    }

}
