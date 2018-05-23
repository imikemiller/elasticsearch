<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 14/05/18
 * Time: 18:01
 */

namespace Basemkhirat\Elasticsearch\Commands;


use Symfony\Component\Console\Input\InputArgument;

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

    public function handle()
    {
        /*
         * Merge in config for the query store index
         */
        $indices = app('config')->get('es.indices');
        $store = require dirname(__FILE__) . '/../config/store.php';
        $indices = array_merge($indices,$store['store']);
        /*
         * Overwrite existing indices config
         */
        app('config')->set('es.indices',$indices);

        /*
         * Index name (set ES_STORE_INDEX and ES_STORE_INDEX_ALIAS)
         */
        $index = array_keys($store['store'])[0];
        $this->info("Creating query storage index: {$index}");
        /*
         * Pass as an argument and call parent
         */
        $this->getDefinition()->addArgument(new InputArgument('index',null,'',$index));
        parent::handle();
    }
}
