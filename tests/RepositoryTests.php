<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 24/05/18
 * Time: 08:02
 */
namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Classes\QueryRepository;
use Basemkhirat\Elasticsearch\Classes\Repositorys\FileRepository;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RecordNotFoundException;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryInterface;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryRecord;
use Basemkhirat\Elasticsearch\Query;
use Illuminate\Support\Collection;

/**
 * Class RepositoryTests
 * @package Basemkhirat\Elasticsearch\Tests
 */
class RepositoryTests extends \PHPUnit_Framework_TestCase
{
    /**
     * See tests/bootstrap.php for mock of the app('config') call
     */
    public function test_that_the_repository_is_resolved_from_config()
    {
        $query = new QueryRepository();
        $this->assertTrue($query->resolveRepository() instanceof RepositoryInterface);
        $this->assertTrue($query->resolveRepository() instanceof FileRepository);
    }

    public function test_that_the_repository_is_overridden()
    {

        $query = new QueryRepository();
        $mockRepository = \Mockery::mock(RepositoryInterface::class);
        $class = get_class($mockRepository);
        $this->assertTrue($query->resolveRepository($mockRepository) instanceof $class);
        $this->assertTrue($query->resolveRepository() instanceof RepositoryInterface);

        /*
         * This should resolve false.
         */
        $this->assertTrue(!$query->resolveRepository($mockRepository) instanceof FileRepository);
    }

    public function test_file_repository()
    {

        $repo = new QueryRepository();

        /*
         * Test initial save
         */
        $query = new Query();
        $query->where('something','else');
        $record = $repo->store($query->getQueryDsl());
        $this->assertInstanceOf(RepositoryRecord::class,$record);

        /*
         * New object to contain the ID (unhydrated)
         */
        $idContainerRecord = new RepositoryRecord($record->getId());

        /*
         * Test the retrieval
         *
         * Object unserialized from the repo
         */
        $retrievedRecord = $repo->retrieve($idContainerRecord);
        $this->assertEquals($record,$retrievedRecord);

        /*
         * Compare original and retrieved Dsl
         */
        $retrievedDsl = $retrievedRecord->getQueryDsl();
        $this->assertEquals($query->getQueryDsl(),$retrievedDsl);

        /*
         * Test the update
         *
         * Change the query on the same ID as $record
         */
        $retrievedDsl->whereNot('one_thing','more');
        $retrievedRecord->setQueryDsl($retrievedDsl);
        $repo->update($retrievedRecord);

        $updatedRecord = $repo->retrieve($idContainerRecord);
        $this->assertEquals($retrievedDsl,$updatedRecord->getQueryDsl());

        /*
         * Add another couple to test the listing
         */
        $repo->store($query->getQueryDsl());
        $repo->store($retrievedDsl);

        $all = $repo->all();
        $this->assertInstanceOf(Collection::class,$all);
        $this->assertEquals(3,$all->count());

        $all->each(function($record) use($repo){
            $this->assertInstanceOf(RepositoryRecord::class,$record);
            $repo->delete($record);
            try{
                /*
                 * This should throw an exception
                 * if the repo has been deleted
                 */
                $repo->retrieve($record);
                $this->assertTrue(false);
            }catch(RecordNotFoundException $exception){
                $this->assertTrue(true);
            }
        });

        /*
         * Test we have no more repos saved
         */
        $all = $repo->all();
        $this->assertInstanceOf(Collection::class,$all);
        $this->assertEquals(0,$all->count());
    }
}