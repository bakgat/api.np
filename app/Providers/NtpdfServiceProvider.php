<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/10/16
 * Time: 18:26
 */

namespace App\Providers;


use App\Domain\Services\Pdf\Ntpdf;
use Illuminate\Support\ServiceProvider;

class NtpdfServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['fpdf'] = $this->app->share(function($app)
        {
            return new Ntpdf();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('fpdf');
    }
}