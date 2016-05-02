<?php namespace browner12\uploader;

use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    /**
     * register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('browner12\uploader\UploaderInterface', 'browner12\uploader\Uploader');
    }

    /**
     * boot the service provider
     *
     * @return void
     */
    public function boot()
    {
        //register commands
        $this->commands([
            Commands\UploaderReprocessCommand::class,
        ]);
        
        //publish config
        $this->publishes([
            __DIR__.'/config/uploader.php' => config_path('uploader.php'),
        ], 'config');
    }
}
