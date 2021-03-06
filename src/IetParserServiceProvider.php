<?php

/**
 * This file contains the IetParserServiceProvider class
 * It makes the functionality available to laravel.
 *
 * PHP version 5.6
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
namespace MrCrankHank\IetParser;

use Illuminate\Support\ServiceProvider;
use MrCrankHank\IetParser\Parser\AclParser;
use MrCrankHank\IetParser\Parser\GlobalOptionParser;
use MrCrankHank\IetParser\Parser\Normalizer;
use MrCrankHank\IetParser\Parser\ProcParser;
use MrCrankHank\IetParser\Parser\TargetParser;

/**
 * Class IetParserServiceProvider.
 *
 * @category Parser
 *
 * @author   Alexander Hank <mail@alexander-hank.de>
 * @license  Apache License 2.0 http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @link     null
 */
class IetParserServiceProvider extends ServiceProvider
{
    /**
     * All commands in this array will be registered with laravel.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AclParser::class, function ($app, $parameters) {
            return new AclParser($parameters['filesystem'], $parameters['filePath'], $parameters['target']);
        }
        );

        $this->app->bind(GlobalOptionParser::class, function ($app, $parameters) {
            return new GlobalOptionParser($parameters['filesystem'], $parameters['filePath'], $parameters['target']);
        }
        );

        $this->app->bind(Normalizer::class, function ($app, $parameters) {
            return new Normalizer($parameters['filesystem'], $parameters['filePath']);
        });

        $this->app->bind(TargetParser::class, function ($app, $parameters) {
            return new TargetParser($parameters['filesystem'], $parameters['filePath'], $parameters['target']);
        });

        $this->app->bind(ProcParser::class, function ($app, $parameters) {
            return new ProcParser($parameters['filesystem'], $parameters['filePath'], $parameters['target']);
        });

        $this->commands($this->commands);
    }
}
