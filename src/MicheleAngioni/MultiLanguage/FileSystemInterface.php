<?php namespace MicheleAngioni\MultiLanguage;

interface FileSystemInterface {

    public function get($path);

    public function put($path, $contents);

    public function delete($paths);

    public function getDirectories($directory);

    public function getFiles($directory);

    public function makeDirectory($path);

}
