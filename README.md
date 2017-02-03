# MULTI LANGUAGE

[![Build Status](https://travis-ci.org/micheleangioni/multi-language.svg)](https://travis-ci.org/micheleangioni/multi-language)
[![License](https://poser.pugx.org/michele-angioni/multi-language/license.svg)](https://packagist.org/packages/michele-angioni/multi-language)
[![Build Status](https://travis-ci.org/micheleangioni/multi-language.svg)](https://travis-ci.org/micheleangioni/multi-language)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/576a839e-7c84-4615-894a-465f30c8f881/small.png)](https://insight.sensiolabs.com/projects/576a839e-7c84-4615-894a-465f30c8f881)

## Introduction

Multi Language is a [Laravel 5.1+](http://laravel.com) package which handles localization. 
It acts as a wrapper for Laravel localization and lets ease translations of your default lang files into new languages.

In case of incompatibilities or errors with Laravel 5.1 - 5.3, or for PHP 5.6, please use [version 0.3](https://github.com/micheleangioni/multi-language/tree/0.3).

## Installation

Multi Language can be installed through Composer, just include `"michele-angioni/multi-language": "~0.4"` to your composer.json and than run `composer update`.

## Configuration

Add the following service providers under the providers array in your app.php configuration file

```php
MicheleAngioni\MultiLanguage\MultiLanguageServiceProvider:class,
MicheleAngioni\MultiLanguage\MultiLanguageBindServiceProvider:class
```

Multi Language can be highly customized by publishing the configuration file through the artisan command artisan command `php artisan vendor:publish`.  
It will create the `ma_multilanguage.php` file in your config directory.
You can than edit the config.php file in your config directory to customize the Multi Language behaviour:

- allowed_languages : it is the number of allowed languages. It can be used to prevent the creation of new supported languages
- allowed_nested_arrays : maximum number of nested arrays allowed in lang files
- language_files_path : path to the language files
- max_text_length : max text length allowed for the languages keys
- safe_mode : enables / disables safe mode. If safe mode is enabled, only files with the same name of files of default locale can be created. Furthermore, only keys already present in the default locale can be added to new languages.

## Usage

The `MicheleAngioni\MultiLanguage\LanguageManager` class is the class that accesses all Multi Language features.
By default it will uses the [Laravel file system manager](http://laravel.com/api/5.4/Illuminate/Filesystem/Filesystem.html) and the [Laravel localization feature](http://laravel.com/docs/5.4/localization).

You can inject it in the constructor of the one of your classes or directly instance it by using the Laravel Application facade `App::make('MicheleAngioni\MultiLanguage\LanguageManager')` and use its methods:

- getLanguagesPath() : get current path to languages files
- setLanguagesPath($path) : set the path to languages files
- setMaxTextLength($value) : set dynamically the max allowed length text
- getDefaultLocale() : get the default locale language. It will be used when a language key is not found in the selected language
- getLocale() : get the current used locale language
- setLocale($locale) : set the current used locale language
- setSafeMode($bool) : change dynamically the Safe Mode setting
- getAvailableLanguages() : get a list of the available languages, that is. the languages which have a directory under the lang directory
- getLanguageFiles($useDefaultLocale = false) : get a list of language files of input language
- getLanguageFile($fileName, $useDefaultLocale = false) : return all keys contained in the input file (without extension)
- getLanguageFileKey($fileName, $key) : return the value of input key in the set locale input file (without extension)
- createNewLanguage($newLocale) : create a new directory under the languages directory for input locale
- writeLanguageFile($fileName, array $inputs) : write the input array on a php file under the /lang/locale directory with input name
- buildArray(array $inputs) : take an array with dot notation and build the final array that will be written into the file
- convertArrayToDotNotation(array $array) : convert an array containing nested arrays to an array with dot notation

## (optional) Custom File System and Translator

By default the Language Manager uses the [Laravel file system manager](http://laravel.com/api/5.4/Illuminate/Filesystem/Filesystem.html) and the [Laravel localization feature](http://laravel.com/docs/5.4/localization).
You can override that by defining your own file system (which has to implement the `MicheleAngioni\MultiLanguage\FileSystemInterface`) and translator (which has to implement the `MicheleAngioni\MultiLanguage\TranslatorInterface`)
The two new files can be injected in the Language Manager constructor by commenint the 'MicheleAngioni\MultiLanguage\LanguageManagerBindServiceProvider' line in the app.php conf file and defining your custom binding in a new service provider.

## Example

Suppose we have a `users.php` file under the app/lang/en directory

```php
/app
├--/controllers
├--/lang
|     └--/en
|          └--users.php
```

which contains

```php
<?php

return array(

    "password" => "Passwords must be at least six characters and match the confirmation.",

    "user" => "We can't find a user with that e-mail address.",

    "token" => "This password reset token is invalid.",

    "sent" => "Password reminder sent!",

);
```

Let's suppose we want to create a Spanish version of the file. We can build a controller handling the language management

```php
<?php

use MicheleAngioni\MultiLanguage\LanguageManager;

class LanguagesController extends \BaseController {

    private $languageManager;

    function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

}
```

and write down some methods to handle the requests.

```php
public function index()
{
    $languages = $this->languageManager->getAvailableLanguages(); // ['en']

    return View::make('languages')->with('languages', $languages)
}
```

The above $languages variable would just be a single value array `['en']` since we only have the `/en` folder under `/lang`.

We now need a list of the English files:

```php
public function showFiles()
{
    $files = $this->languageManager->getLanguageFiles(); // ['users']

    return View::make('languagesFiles')->with('files', $files)
}
```

The showFiles() method would just return `['users']` as we have just one file in our `/lang/en` folder.

Let's examine the content of the file

```php
// $fileName = 'users';
public function showFile($fileName)
{
    $file = $this->languageManager->getLanguageFile($fileName)

    return View::make('languagesFile')->with('file', $file)
}
```

The above method returns an array with the file content.

Let's now create a Spanish version. First of all we must create the `/es` folder under the `/lang` folder

```php
public function createNewLanguage()
{
    // [...] Input validation

    $this->languageManager->createNewLanguage($input['locale']);

    return Redirect::route('[...]')->with('ok', 'New language successfully created.');
}
```

We then obviously need a view to submit the Spanish sentences and we leave it up to you.
An associative array with **key => sentence** structure must be sent from the view to the following method

```php
public function saveFile($locale, $fileName)
{
    // [...] Input validation

    $this->languageManager->setLocale($languageCode);

    $this->languageManager->writeLanguageFile($fileName, $input['all']);

    return Redirect::route('[...]')->with('ok', 'Lang file successfully saved.');
}
```

## Contribution guidelines

Support follows PSR-1, PSR-2 and PSR-4 PHP coding standards and semantic versioning.

Please report any issue you find in the issues page. Pull requests are welcome.

## License

Support is free software distributed under the terms of the MIT license.

## Contacts

Feel free to contact me.
