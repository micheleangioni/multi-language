<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Allowed languages
    |--------------------------------------------------------------------------
    |
    | Max number of languages allowed. It can be used to prevent the creation
    | of new supported languages.
    |
    */

    'allowed_languages' => 10,

    /*
    |--------------------------------------------------------------------------
    | Allowed nested arrays
    |--------------------------------------------------------------------------
    |
    | Max number of nested arrays allowed in lang files.
    |
    */

    'allowed_nested_arrays' => 3,

    /*
    |--------------------------------------------------------------------------
    | Language files path
    |--------------------------------------------------------------------------
    |
    | Path to the language files. Base path is the /app directory.
    |
    */

    'language_files_path' => '/lang',

    /*
    |--------------------------------------------------------------------------
    | Max text length
    |--------------------------------------------------------------------------
    |
    | Max text length allowed for the lang keys.
    |
    */

    'max_text_length' => 100,

    /*
    |--------------------------------------------------------------------------
    | Safe mode
    |--------------------------------------------------------------------------
    |
    | Enables safe mode by default. If safe mode is enabled, only files with
    | the same name of files of default locale can be created.
    | Furthermore, only keys already present in the default locale can be added.
    |
    */

    'safe_mode' => true,

);