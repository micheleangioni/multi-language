# MULTI LANGUAGE

## Introduction

Multi Language is a [Laravel 4](http://laravel.com) package which handles the project localization. It acts as a wrapper for the laravel localization package and lets easy translation of your lang files into new languages.

## Installation

Multi Language can be installed through Composer, just include `"angioni/multi-language": "dev-master"` to your composer.json.

## Configuration

Add the following service service providers under the providers array in your app.php configuration file

    'MicheleAngioni\MultiLanguage\LanguageManagerServiceProvider',
    'MicheleAngioni\MultiLanguage\LanguageManagerBindServiceProvider'

Multi Language can be highly customized by publishing the configuration file through the artisan command `php artisan config:publish angioni/multi-language`.

You can than edit the config.php file in your `app/config/packages/angioni/multi-language` directory to customize the support behaviour:

- allowed_languages : it is the number of allowed languages. It can be used to prevent the creation of new supported languages
- allowed_nested_arrays : maximum number of nested arrays allowed in lang files
- language_files_path : path to the language files
- max_text_length : max text length allowed for the languages keys
- safe_mode : enables / disables Safe Mode. If safe mode is enabled, only files with the same name of files of default locale can be created. Furthermore, only keys already present in the default locale can be added.

## Usage

The `MicheleAngioni\MultiLanguage\LanguageManager` class is the class that accesses all Multi Language features.
By default it will uses the [Laravel file system manager](http://laravel.com/api/4.2/Illuminate/Filesystem/Filesystem.html) and the [Laravel localization feature](http://laravel.com/docs/4.2/localization).

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

By default the Language Manager uses the [Laravel file system manager](http://laravel.com/api/4.2/Illuminate/Filesystem/Filesystem.html) and the [Laravel localization feature](http://laravel.com/docs/4.2/localization).
You can override that by defining your own file system (which has to implement the `MicheleAngioni\MultiLanguage\FileSystemInterface`) and translator (which has to implement the `MicheleAngioni\MultiLanguage\TranslatorInterface`)
The two new files can be injected in the Language Manager constructor by commenint the 'MicheleAngioni\MultiLanguage\LanguageManagerBindServiceProvider' line in the app.php conf file and defining your custom binding in a new service provider.

## Contribution guidelines

Pull requests are welcome. Breaking changes won't be merged.

## License

Support is free software distributed under the terms of the MIT license.

## Contacts

Feel free to contact me.