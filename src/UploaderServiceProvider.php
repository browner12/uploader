<?php namespace browner12\uploader;

use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    /**
     * register
     */
    public function register()
    {
        $this->app->bind('browner12\uploader\UploaderInterface', 'browner12\uploader\Uploader');
    }

    /**
     * boot
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/uploader.php' => config_path('uploader.php'),
        ]);
    }
}
