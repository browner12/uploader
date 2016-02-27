# Uploader

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Uploader is a package that provides a simple interface to upload image and document assets to your website in a consistent manner.

## Install

Via Composer

``` bash
$ composer require browner12/uploader
```

## Setup

Add the service provider to the providers array in  `config/app.php`.

``` php
'providers' => [
    browner12\uploader\UploaderServiceProvider::class,
];
```

## Publishing

You can publish everything at once

``` php
php artisan vendor:publish --provider="browner12\uploader\UploaderServiceProvider"
```

or you can publish groups individually.

``` php
php artisan vendor:publish --provider="browner12\uploader\UploaderServiceProvider" --tag="config"
```

## Configuration

Setting up your new `uploader.php` configuration file is very important to the uploader behaving as you expect. Each option is well documented in the file, but we will also address them here, due to their integral role.

First off we have the `base_directory`. This is where *all* of your uploads will be stored, relative to your resource root (most likely the `public` directory). While you may choose to leave this option blank, one benefit to having a directory for all user generated content is it is easy to add to your `.gitignore`, and it is easier to transfer if needed.

``` php
'base_directory' => '',
```

Next we have the `original_directory`, `optimized_directory`, and `thumbnail_directory`. When you upload an image, by default, the package will automatically create optimized and thumbnail versions of the file, which can drastically help improve page loads. With these options, you can set the name of the directory each goes into.

``` php
'original_directory' => 'original',
'optimized_directory' => '',
'thumbnail_directory' => 'thumbnail',
```

Next you can set if you want optimized images and thumbnails to be automatically created when you upload an image. By default, they are turned on because this can aid in drastically reducing your bandwidth usage and load times.

``` php
'create_optimized' => true,
'create_thumbnails' => true,
```

Next you will set the default extensions and mime types for each type of upload. We include some sensible defaults.

``` php
'document_extensions' => ['pdf', 'doc', 'docx', 'ppt'],
'image_extensions' => ['jpg', 'jpeg', 'gif', 'png'],
'video_extensions' => ['avi', 'mov', 'mp4', 'ogg'],
'audio_extensions' => ['mp3', 'wav'],

'document_mime_types_' => ['application/pdf', 'application/msword'], //other defaults omitted for brevity
'image_mime_types_' => ['image/gif', 'image/jpeg', 'image/png'],
'video_mime_types_' => ['video/avi', 'video/quicktime', 'video/mp4', 'video/ogg'],
'audio_mime_types_' => [ 'audio/mpeg', 'audio/mpeg3', 'audio/wav'],
```

The maximum upload size is the largest file size (in bytes) you will accept for an upload. Remember that if this value is larger than the maximum upload size of your server, it could result in errors.

``` php
'maximum_upload_size' => 32000000,
```

When you upload images, and an optimized image is created, two properties will affect the optimized image. First, you can set the quality of the new image to a value between 1 and 100. You may also set a maximum width of the optimized image. This can be helpful to keep the file size down as well, because simply changing the quality of a very large image, is still going to result in a very large file size. If you do not wish to constrain the width, set the value to 0.

``` php
'optimized_image_quality' => 60,
'optimized_maximum_width' => 1000,
```

Lastly, you can set the width of the generated thumbnails.

``` php
'thumbnail_width' => 100,
```

## Usage

Start by manually instantiating the uploader

``` php
$uploader = new Uploader();
```

or using dependency injection.

``` php
public function __construct(UploaderInterface $uploader)
{
    $this->uploader = $uploader;
}
```

There are four main methods with the uploader, each for uploading a different type of file.

``` php
$this->uploader->document($file, $path, $filename);
$this->uploader->image($file, $path, $filename);
$this->uploader->video($file, $path, $filename);
$this->uploader->audio($file, $path, $filename);
```

One important thing to note is that `$file` must be an instance of `\Symfony\Component\HttpFoundation\File\UploadedFile`. If you are using Laravel, all files will be passed as this object automatically. The `$path` you pass will be relative to your `base_directory` defined in your configuration. If you omit the `$filename`, the original name of the file will be used. If you choose this option please be aware that files with the same name will be overwritten. Please also note that irregular file names may cause unexpected issues. We may choose to address this in a future version of the package.

Let's look at an example of how to best use the uploader inside of a Laravel controller.

``` php
public function store(Request $request)
{
    try{

        if($request->hasFile('image')){
            
            $file = $this->uploader->image($request->file('image'), 'dogs', 1);
        }
    }
    catch(browner12\uploader\UploaderException $e){
    
        //handle any errors here
    }
    
    var_dump($file);
}
```

Notice the uploader also returns information to you. The `$file` will be an array with information similar to the following:

``` php
array(9) {
    ["id"] => string(7) "1.jpg"
    ["name"] => string(11) "1.jpg"
    ["size"] => int(97065)
    ["mime_type"] => string(10) "image/jpeg"
    ["extension"] => string(3) "jpg"
    ["original_name"] => string(7) "1.jpg"
    ["url"] => string(34) "content/image/original/1.jpg"
    ["optimized_url"] => string(14) "content/image/1.jpg"
    ["thumbnail_url"] => string(24) "content/image/thumbnail/1.jpg"
}
```

By default the image uploader will make optimized and thumbnail versions of your file. The original, optimized, and thumbnail versions will be placed in the directories specified in your configuration. The reason these additional files are created for images is the help optimize bandwidth usages as much as possible. For example, if a user uploads a 25MB image file, you most likely do not want to display that image every time it is requested. Rather, you can display the optimized file, which can have a significantly smaller file size. If you only need a small version of the image, request the thumbnail instead. It will most likely have an even smaller file size. The use of optimized and thumbnail images will greatly help you improve load times, and performance. If you do not wish to create these additional files, you may update your configuration to turn those off.

While the majority of the time you will only need to use your default configuration, there may be times when you wish to change a value simply for one upload. The package has setters to let you do just that. They should be called prior to uploading your file.

``` php
$this->uploader->setDirectory('base', 'newBaseDirectory');
$this->uploader->setDirectory('original', 'newOriginalDirectory');
$this->uploader->setDirectory('optimized', 'newOptimizedDirectory');
$this->uploader->setDirectory('thumbnail', 'newThumbnailDirectory');

$this->uploader->setCreateOptimized(false);
$this->uploader->setCreateThumbnails(false);

$this->uploader->setValidExtensions('image', ['jpg']);
$this->uploader->setValidMimeTypes('image', ['image/jpeg']);

$this->uploader->setMaximumUploadSize(10000000);
$this->uploader->setOptimizedImageQuality(60);
$this->uploader->setOptimizedMaximumWidth(500);
$this->uploader->setThumbnailWidth(200);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email browner12@gmail.com instead of using the issue tracker.

## Credits

- [Andrew Brown][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/browner12/uploader.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/browner12/uploader/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/browner12/uploader.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/browner12/uploader.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/browner12/uploader.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/browner12/uploader
[link-travis]: https://travis-ci.org/browner12/uploader
[link-scrutinizer]: https://scrutinizer-ci.com/g/browner12/uploader/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/browner12/uploader
[link-downloads]: https://packagist.org/packages/browner12/uploader
[link-author]: https://github.com/browner12
[link-contributors]: ../../contributors
