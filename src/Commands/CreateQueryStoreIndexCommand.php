<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 14/05/18
 * Time: 18:01
 */

namespace Basemkhirat\Elasticsearch\Commands;


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
        app()->mergeConfigFrom(
            dirname(__FILE__) . '/config/store.php', 'es'
        );

        $index = config('es.store.index');
        $this->info("Creating query storage index: {$index}");
        $args = ['index'=>$index];
        $args = $this->option('connection')?$args['connection']=$this->option('connection'):$args;

        $this->call('es:indices:create',$args);

    }
}
