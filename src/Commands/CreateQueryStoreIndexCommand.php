<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 14/05/18
 * Time: 18:01
 */

namespace Basemkhirat\Elasticsearch\Commands;


use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CreateQueryStoreIndexCommand
 * @package Basemkhirat\Elasticsearch\Commands
 */
class CreateQueryStoreIndexCommand extends CreateIndexCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:indices:create:store {--connection= : Elasticsearch connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new index using defined setting and mapping in config file';

    /**
     * @return mixed|void
     */
    public function handle()
    {
        /*
         * Find query store index
         */
        $store = require dirname(__FILE__) . '/../config/store.php';
        $index = array_keys($store['store'])[0];
        /*
         * Pass as an argument and call parent
         */
        $this->getDefinition()->addArgument(new InputArgument('index',null,'',$index));

        $this->confirm("Creating query storage index: {$index}",function(){
            parent::handle();
        });
    }
}
