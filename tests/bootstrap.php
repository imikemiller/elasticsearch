<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 24/05/18
 * Time: 08:57
 */

use Basemkhirat\Elasticsearch\Classes\Repositorys\FileRepository;

require_once __DIR__.'/../vendor/autoload.php';


if(!function_exists('app')){

    function app($arg = null)
    {
        $container = \Mockery::mock(\Illuminate\Container\Container::class);

        /*
         * Mock any laravel/lumen container methods here...
         */
        if($arg ==='config') {
            $container->shouldReceive('get')->with('es.store.driver')->andReturn(FileRepository::class);
        }

        $container->shouldReceive('basepath')->andReturn(__DIR__.'/../src');
        return $container;
    }
}