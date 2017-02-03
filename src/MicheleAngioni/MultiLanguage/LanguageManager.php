<?php

namespace MicheleAngioni\MultiLanguage;

use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use InvalidArgumentException;
use MicheleAngioni\MultiLanguage\Exceptions\DirectoryNotCreatedException;
use MicheleAngioni\MultiLanguage\Exceptions\FileNameNotSafeException;
use MicheleAngioni\MultiLanguage\Exceptions\InvalidFileNameException;
use MicheleAngioni\MultiLanguage\Exceptions\KeysNotSafeException;
use MicheleAngioni\MultiLanguage\Exceptions\LanguageNotFoundException;
use MicheleAngioni\MultiLanguage\Exceptions\TextTooLongException;
use MicheleAngioni\MultiLanguage\Exceptions\TooManyNestedArraysException;

class LanguageManager
{

    /**
     * Laravel Application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var FileSystemInterface
     */
    protected $fileSystem;

    /**
     * @var string
     */
    protected $languagesPath;

    /**
     * Allowed numbers of languages.
     *
     * @var int
     */
    protected $allowedLanguages = 10;

    /**
     * Number of nesting level for arrays in the file.
     *
     * @var int
     */
    protected $allowedNestedArrays = 3;

    /**
     * Max number of allowed characters for a language text.
     *
     * @var int
     */
    protected $maxTextLength = 100;

    /**
     * If safe mode is enabled, only files with the same name of files of default locale can be created.
     * Furthermore, only keys already present in the default locale can be added.
     *
     * @var bool
     */
    protected $safeMode = true;

    /**
     * @var TranslatorContract
     */
    protected $translator;

    /**
     * Language Manager.
     *
     * @param  FileSystemInterface $fileSystem
     * @param  TranslatorContract $translator
     */
    public function __construct(FileSystemInterface $fileSystem, TranslatorContract $translator, $app = null)
    {
        $this->app = $app ?: app();

        $this->allowedLanguages = $this->app['config']->get('ma_multilanguage.allowed_languages');

        $this->allowedNestedArrays = $this->app['config']->get('ma_multilanguage.allowed_nested_arrays');

        $this->defaultLocale = $this->app['config']->get('app.fallback_locale');

        $this->fileSystem = $fileSystem;

        $this->maxTextLength = $this->app['config']->get('ma_multilanguage.max_text_length');

        $this->safeMode = $this->app['config']->get('ma_multilanguage.safe_mode');

        $this->translator = $translator;

        $this->languagesPath = $this->app['path'] . DIRECTORY_SEPARATOR . '..' . $this->app['config']->get('ma_multilanguage.language_files_path');
    }

    /**
     * Get the current Language path.
     *
     * @return string
     */
    public function getLanguagesPath()
    {
        return $this->languagesPath;
    }

    /**
     * Set the path to the directory containing the language directories.
     *
     * @param  string $path
     */
    public function setLanguagesPath(string $path)
    {
        $this->languagesPath = $path;
    }

    /**
     * Set the max allowed length of a language text or key.
     *
     * @param  int $value
     */
    public function setMaxTextLength(int $value)
    {
        $this->maxTextLength = $value;
    }

    /**
     * Get default locale.
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Get current locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    /**
     * Set new locale.
     *
     * @param  string $locale
     *
     * @throws LanguageNotFoundException
     */
    public function setLocale(string $locale)
    {
        if (!in_array($locale, $this->getAvailableLanguages())) {
            throw new LanguageNotFoundException("Language $locale not found.");
        }

        $this->translator->setLocale($locale);
    }

    /**
     * Set the safe mode.
     *
     * @param  bool $bool
     *
     * @throws InvalidArgumentException
     */
    public function setSafeMode(bool $bool)
    {
        $this->safeMode = $bool;
    }

    /**
     * Return the list of available languages.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        $languages = [];

        $paths = $this->fileSystem->getDirectories($this->getLanguagesPath());

        foreach ($paths as $path) {
            $languages[] = substr($path, strlen($this->getLanguagesPath()) + 1);
        }

        return $languages;
    }

    /**
     * Return the list of the set locale files (without extension).
     *
     * @param  bool $useDefaultLocale = false
     *
     * @return array
     */
    public function getLanguageFiles(bool $useDefaultLocale = false)
    {
        $files = [];

        if ($useDefaultLocale) {
            $locale = $this->getDefaultLocale();
        } else {
            $locale = $this->getLocale();
        }

        $paths = $this->fileSystem->getFiles($this->getLanguagesPath() . DIRECTORY_SEPARATOR . $locale);

        foreach ($paths as $path) {
            $completeFileName = substr($path, strlen($this->getLanguagesPath()) + strlen($locale) + 2);

            $files[] = array_pad(explode(".", $completeFileName), 2, '.ext')[0];
        }

        return $files;
    }

    /**
     * Return all keys contained in the set locale input file (without extension).
     *
     * @param  string $fileName
     * @param  bool $useDefaultLocale
     *
     * @throws InvalidFileNameException
     *
     * @return array
     */
    public function getLanguageFile(string $fileName, bool $useDefaultLocale = false)
    {
        // Check the input file name
        if (!$this->checkFileName($fileName)) {
            throw new InvalidFileNameException("Input file name is not valid.");
        }

        if (!$useDefaultLocale) {
            return $this->translator->get($fileName);
        }

        $currentLocale = $this->getLocale();
        $this->setLocale($this->getDefaultLocale());

        $languageFile = $this->translator->get($fileName);

        $this->setLocale($currentLocale);

        return $languageFile;
    }

    /**
     * Return the value of input key in the set locale input file (without extension).
     *
     * @param  string $fileName
     * @param  string $key
     *
     * @throws InvalidFileNameException
     *
     * @return string
     */
    public function getLanguageFileKey(string $fileName, string $key)
    {
        // Check the input file name
        if (!$this->checkFileName($fileName)) {
            throw new InvalidFileNameException("Input file name is not valid.");
        }

        return $this->translator->get("$fileName.$key");
    }

    /**
     * Create a new directory under the languages directory for input locale.
     * Return true on success.
     *
     * @param  string $newLocale
     *
     * @throws DirectoryNotCreatedException
     *
     * @return bool
     */
    public function createNewLanguage(string $newLocale)
    {
        if (count($this->getAvailableLanguages()) >= $this->allowedLanguages) {
            throw new DirectoryNotCreatedException("Max number of allowed languages alread reached.");
        }

        if (!$this->fileSystem->makeDirectory($this->getLanguagesPath() . DIRECTORY_SEPARATOR . $newLocale)) {
            throw new DirectoryNotCreatedException("New directory for locale $newLocale cannot be created.");
        }

        return true;
    }

    /**
     * Write the input array on a php file under the /lang/locale directory with input name.
     * Return true on success.
     *
     * @param  string $fileName
     * @param  array $inputs
     *
     * @throws FileNameNotSafeException
     * @throws InvalidFileNameException
     * @throws KeysNotSafeException
     *
     * @return bool
     */
    public function writeLanguageFile(string $fileName, array $inputs)
    {
        // Check if same mode is enabled, in case check if $fileName is safe
        if ($this->safeMode) {
            if (!$this->checkIfFileNameIsSafe($fileName)) {
                throw new FileNameNotSafeException("Input file name for language file is not safe.");
            };
        }

        // Check the input file name
        if (!$this->checkFileName($fileName)) {
            throw new InvalidFileNameException("Input file name is not valid.");
        }

        // Purify Inputs
        $inputs = $this->purifyInputs($inputs);

        // Handle dot notation and build the array
        $inputs = $this->buildArray($inputs);

        if ($this->safeMode) {
            if (!$this->checkIfKeysAreSafe($fileName, $inputs)) {
                throw new KeysNotSafeException("Input keys for language file are not safe.");
            };
        }

        $fileName = $this->languagesPath . DIRECTORY_SEPARATOR . $this->getLocale() . DIRECTORY_SEPARATOR . $fileName . '.php';

        $content = '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($inputs, true) . ';';

        $this->fileSystem->put($fileName, $content);

        return true;
    }

    /**
     * Escape input array. Keys beginning with _ are discarded. If the file already exists, it gets overwritten.
     *
     * @param  array $inputs
     *
     * @throws InvalidArgumentException
     * @throws TextTooLongException
     *
     * @return array
     */
    protected function purifyInputs(array $inputs)
    {
        $newInputs = [];

        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                throw new InvalidArgumentException('Input array can NOT contain nested arrays.');
            }

            if (strlen((string)$key) > $this->maxTextLength) {
                throw new TextTooLongException("Input array has at least one key whose name exceeds the max length allowed.");
            }

            if (strlen((string)$value) > $this->maxTextLength) {
                throw new TextTooLongException("Input array has at least one language value whose value exceeds the max length allowed.");
            }

            $newInputs[e($key)] = e($value);
        }

        return $newInputs;
    }

    /**
     * Check if the input filename contains potential path traversal characters.
     *
     * @param  string $fileName
     *
     * @return bool
     */
    protected function checkFileName(string $fileName)
    {
        if (substr_count($fileName, '../') || substr_count($fileName, '..\\')) {
            return false;
        }

        return true;
    }

    /**
     * Build the final array that will be written into the file. Support dot notation.
     *
     * @param  array $inputs
     *
     * @throws InvalidArgumentException
     * @throws TooManyNestedArraysException
     *
     * @return array
     */
    public function buildArray(array $inputs)
    {
        $newArray = [];

        foreach ($inputs as $path => $value) {
            if (is_array($value)) {
                throw new InvalidArgumentException('Input array CANNOT contain nested arrays.');
            }

            if (isset($path[0])) {
                if ($path[0] === '_' || $path[0] === '.') {
                    unset($inputs[$path]);
                    continue;
                }
            }

            $this->assignArrayByDotPath($newArray, $path, $value);
        }

        return $newArray;
    }

    /**
     * Take a path of nested array with dot notation and build an array, saving it through reference.
     *
     * @param  array $arr
     * @param  string $path
     * @param  string $value
     *
     * @throws InvalidArgumentException
     * @throws TooManyNestedArraysException
     */
    protected function assignArrayByDotPath(&$arr, string $path, string $value)
    {
        $keys = explode('.', $path);

        // Check that no key have a '0' value
        // This is because the '0' key is not read
        foreach ($keys as $keyValue) {
            if ($keyValue == '0') {
                throw new InvalidArgumentException('Invalid key in input array: part of the key in dot notation can NOT be \'0\'.');
            }
        }

        // Check if the allowed maximum level of nested array is exceeded
        $nestingLevels = count($keys);

        if ($nestingLevels > $this->allowedNestedArrays) {
            throw new TooManyNestedArraysException("Too many nested arrays in the input arrays. Maximum allowed is: " . $this->allowedNestedArrays);
        }

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    /**
     * Convert an array with nested arrays into a dot notation array.
     *
     * @param  array $array
     *
     * @return array
     */
    public function convertArrayToDotNotation(array $array)
    {
        $recursiveIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

        $newArray = [];

        foreach ($recursiveIterator as $leafValue) {
            $keys = [];

            foreach (range(0, $recursiveIterator->getDepth()) as $depth) {
                $keys[] = $recursiveIterator->getSubIterator($depth)->key();
            }

            $newArray[implode('.', $keys)] = $leafValue;
        }

        return $newArray;
    }

    /**
     * Check if input keys are safe, i.e. all its keys are contained in the default locale language file.
     *
     * @param  string $fileName
     * @param  array $array
     *
     * @return bool
     */
    protected function checkIfKeysAreSafe(string $fileName, array $array)
    {
        $languageFile = $this->getLanguageFile($fileName, true);

        foreach ($array as $key => $value) {
            if (isset($key[0])) {
                if ($key[0] === '_' || $key[0] === '.') {
                    continue;
                }
            }

            if (!array_key_exists($key, $languageFile)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if input file name is safe, i.e. if the default locale contains a file with the same name.
     *
     * @param  string $fileName
     *
     * @return bool
     */
    protected function checkIfFileNameIsSafe(string $fileName)
    {
        $allowedFileNames = $this->getLanguageFiles(true);

        if (!in_array($fileName, $allowedFileNames)) {
            return false;
        }

        return true;
    }

}
