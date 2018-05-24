<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 24/05/18
 * Time: 08:02
 */
namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Classes\QueryRepository;
use Basemkhirat\Elasticsearch\Classes\Repositorys\ESRepository;
use Basemkhirat\Elasticsearch\Classes\Repositorys\FileRepository;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryInterface;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryRecord;
use Basemkhirat\Elasticsearch\Query;
use Mockery\Mock;

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
        $query = new Query();
        $query->where('something','else');
        $repo = new QueryRepository();
        $record = $repo->store($query->getQueryDsl());
        $this->assertTrue($record instanceof RepositoryRecord);

        /*
         * New object to contain the ID (unhydrated)
         */
        $idContainerRecord = new RepositoryRecord($record->getId());

        /*
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
         * Change the query on the same ID as $record
         */
        $retrievedDsl->whereNot('one_thing','more');
        $retrievedRecord->setQueryDsl($retrievedDsl);
        $repo->update($retrievedRecord);

        $updatedRecord = $repo->retrieve($idContainerRecord);
        $this->assertEquals($retrievedDsl,$updatedRecord->getQueryDsl());

    }
}