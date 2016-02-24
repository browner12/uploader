<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Directory
    |--------------------------------------------------------------------------
    |
    | If you would like all uploads to be stored in one directory, you may
    | set it here. This makes it easier to gitignore your uploaded
    | content if it is all stored in one directory. This is
    | relative to your web root.
    |
    */

    'base_directory' => '/',

    /*
    |--------------------------------------------------------------------------
    | Original Directory
    |--------------------------------------------------------------------------
    |
    | This directory will include the original images.
    |
    */

    'original_directory' => '/original',

    /*
    |--------------------------------------------------------------------------
    | Optimized Directory
    |--------------------------------------------------------------------------
    |
    | This directory will include the optimized images.
    |
    */

    'optimized_directory' => '/',

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Directory
    |--------------------------------------------------------------------------
    |
    | This directory will include the thumbnail images.
    |
    */

    'thumbnail_directory' => '/thumbnail',

    /*
    |--------------------------------------------------------------------------
    | Directory Mapper
    |--------------------------------------------------------------------------
    |
    | In order to provide consistency with the location of uploaded files,
    | a mapper will define which path files will be uploaded to. These
    | paths will be relative to your base_directory.
    |
    */

    'mapper' => [],

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
    | Video Extensions
    |--------------------------------------------------------------------------
    |
    | Set the default valid video extensions.
    |
    */

    'video_extensions' => ['avi', 'mov', 'mp4', 'ogg'],

    /*
    |--------------------------------------------------------------------------
    | Audio Extensions
    |--------------------------------------------------------------------------
    |
    | Set the default valid audio extensions.
    |
    */

    'audio_extensions' => ['mp3', 'wav'],

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
    | Video Mime Types
    |--------------------------------------------------------------------------
    |
    | Set the default valid video mime types.
    |
    */

    'video_mime_types' => [
        'video/avi',
        'video/quicktime',
        'video/mp4',
        'video/ogg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audio Mime Types
    |--------------------------------------------------------------------------
    |
    | Set the default valid audio mime types.
    |
    */

    'audio_mime_types' => [
        'audio/mpeg3',
        'audio/wav',
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload Size
    |--------------------------------------------------------------------------
    |
    | Set the default maximum upload size (in kilobytes).
    |
    */

    'maximum_upload_size' => 32000000,

    /*
    |--------------------------------------------------------------------------
    | Optimized Image Quality
    |--------------------------------------------------------------------------
    |
    | Set the quality of optimized images. Values are between 1 and 100.
    |
    */

    'optimized_image_quality' => 60,

    /*
    |--------------------------------------------------------------------------
    | Optimized Maximum Width
    |--------------------------------------------------------------------------
    |
    | Set the the maximum width of generated optimized images. Set the value
    | to 0 for no maximum width.
    |
    */

    'optimized_maximum_width' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Width
    |--------------------------------------------------------------------------
    |
    | Set the width of generated thumbnails. The default value is 100.
    |
    */

    'thumbnail_width' => 100,

];
