<?php

/**
 * Created by PhpStorm.
 * User: ra3oul
 * Date: 6/15/16
 * Time: 12:10 PM
 */
namespace ra3oul\EloquentAbstractRepository;


use Illuminate\Support\ServiceProvider;


/**
 * Class EloquentAbstractRepositoryServiceProvider
 * @package ra3oul\EloquentAbstractRepository
 */
class EloquentAbstractRepositoryServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {

        $this->app->bind('ra3oul\EloquentAbstractRepository\repository\RepositoryInterface',
            'ra3oul\EloquentAbstractRepository\repository\eloquent\AbstractEloquentRepository');


    }

    /**
     *
     */
    public function boot()
    {

    }

}
