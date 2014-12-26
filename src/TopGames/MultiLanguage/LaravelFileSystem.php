<?php

namespace TopGames\MultiLanguage;

use Illuminate\Filesystem\Filesystem;

class LaravelFileSystem implements FileSystemInterface {

    protected $filesystem;


    function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }


    public function get($path)
    {
        return $this->filesystem->get($path);
    }

    public function put($path, $contents)
    {
        return $this->filesystem->put($path, $contents);
    }

    public function delete($paths)
    {
        return $this->filesystem->delete($paths);
    }

    public function getDirectories($directory)
    {
        return $this->filesystem->directories($directory);
    }

    public function getFiles($directory)
    {
        return $this->filesystem->files($directory);
    }

    public function makeDirectory($path)
    {
        return $this->filesystem->makeDirectory($path);
    }

}