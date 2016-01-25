<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Directory
    |--------------------------------------------------------------------------
    |
    | Select the base directory to contain all uploads.  This is relative to your
    | web root.
    |
    */

    'base_directory' => '/content',

    /*
    |--------------------------------------------------------------------------
    | Original Directory
    |--------------------------------------------------------------------------
    |
    | This directory will include the originals of uploaded images.
    |
    */

    'original' => '/original',

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Directory
    |--------------------------------------------------------------------------
    |
    | This directory will include the thumbnails of uploaded images.
    |
    */

    'thumbnail' => '/thumbnail',

    /*
    |--------------------------------------------------------------------------
    | Document Extensions
    |--------------------------------------------------------------------------
    |
    | Set the default valid document extensions.
    |
    */

    'document_extensions' => ['pdf', 'doc', 'docx', 'ppt'],

    /*
    |--------------------------------------------------------------------------
    | Image Extensions
    |--------------------------------------------------------------------------
    |
    | Set the default valid image extensions.
    |
    */

    'image_extensions' => ['jpg', 'jpeg', 'gif', 'png'],

    /*
    |--------------------------------------------------------------------------
    | Document Mime Types
    |--------------------------------------------------------------------------
    |
    | Set the default valid document mime types.
    |
    */

    'document_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Mime Types
    |--------------------------------------------------------------------------
    |
    | Set the default valid image mime types.
    |
    */

    'image_mime_types' => ['image/gif', 'image/jpeg', 'image/png'],

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload Size
    |--------------------------------------------------------------------------
    |
    | Set the default maximum upload size (in kilobytes).
    |
    */

    'maximum_upload_size' => 32000000,

];
