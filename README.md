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

## Usage

Start by manually instantiating the uploader

``` php
$uploader = new Uploader();
```

or using dependency injection.

``` php
public function __construct(Uploader $uploader)
{
    $this->uploader = $uploader;
}
```

There are four main methods with the uploader, each for uploading a different type of file.

``` php
$uploader->document($file, $path, $filename);
$uploader->picture($file, $path, $filename);
$uploader->video($file, $path, $filename);
$uploader->audio($file, $path, $filename);
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
