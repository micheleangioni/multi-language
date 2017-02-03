<?php

class LanguageManagerTest extends PHPUnit_Framework_TestCase {


	public function testSetLanguagesPath()
	{
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager->makePartial();

        $languageManager->setLanguagesPath('testLanguagesPath');

        $this->assertEquals('testLanguagesPath', $languageManager->getLanguagesPath());
    }

    public function testSetLocale()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $mockTranslator
            ->shouldReceive('setLocale')
            ->once()
            ->with('it');

        $mockTranslator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('it');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager
            ->shouldReceive('getAvailableLanguages')
            ->once()
            ->andReturn(['en', 'it']);

        $languageManager->makePartial();

        $languageManager->setLocale('it');

        $this->assertEquals('it', $languageManager->getLocale());
    }

    /**
     * @expectedException MicheleAngioni\MultiLanguage\Exceptions\LanguageNotFoundException
     */
    public function testSetLocaleFailing()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager
            ->shouldReceive('getAvailableLanguages')
            ->once()
            ->andReturn(['en', 'es']);

        $languageManager->makePartial();

        $languageManager->setLocale('it');
    }

    public function testGetAvailableLanguages()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager
            ->shouldReceive('getLanguagesPath')
            ->atLeast()
            ->once()
            ->andReturn('testLanguagesPath');

        $languageManager->makePartial();

        $mockFileSystem
            ->shouldReceive('getDirectories')
            ->once()
            ->with('testLanguagesPath')
            ->andReturn(array(
                  0 => 'testLanguagesPath/en',
                  1 => 'testLanguagesPath/it'
            ));

        $languages = $languageManager->getAvailableLanguages();

        $this->assertContains('en', $languages);
        $this->assertContains('it', $languages);
    }

    public function testGetLanguageFiles()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $mockTranslator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('en');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager->makePartial();

        $languageManager
            ->shouldReceive('getLanguagesPath')
            ->atLeast()
            ->once()
            ->andReturn('testLanguagesPath');

        $mockFileSystem
            ->shouldReceive('getFiles')
            ->once()
            ->andReturn(array(
              0 => 'testLanguagesPath/it/test1.php',
              1 => 'testLanguagesPath/it/test2.php'
            ));

        $files = $languageManager->getLanguageFiles();

        $this->assertContains('test1', $files);
        $this->assertContains('test2', $files);
    }

    public function testGetLanguageFile()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $fileName = 'test1';

        $mockTranslator
            ->shouldReceive('get')
            ->with($fileName)
            ->once()
            ->andReturn(array(
                'key1' => 'value1',
                'key2' => 'value2'
            ));

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager->makePartial();

        $file = $languageManager->getLanguageFile($fileName);

        $this->assertArrayHasKey('key1', $file);
        $this->assertContains('value1', $file);
        $this->assertArrayHasKey('key2', $file);
        $this->assertContains('value2', $file);
    }

    public function testGetLanguageFileKey()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $fileName = 'test1';
        $key = 'key1';

        $mockTranslator
            ->shouldReceive('get')
            ->with("$fileName.$key")
            ->once()
            ->andReturn('value1');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $languageManager->makePartial();

        $value = $languageManager->getLanguageFileKey($fileName, $key);

        $this->assertEquals('value1', $value);
    }

    public function testCreateNewLanguage()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $newLanguage = 'it';

        $languageManager
            ->shouldReceive('getAvailableLanguages')
            ->once()
            ->andReturn(['en']);

        $languageManager
            ->shouldReceive('getLanguagesPath')
            ->atLeast()
            ->once()
            ->andReturn('testLanguagesPath');

        $mockFileSystem
            ->shouldReceive('makeDirectory')
            ->with('testLanguagesPath' . DIRECTORY_SEPARATOR . $newLanguage)
            ->once()
            ->andReturn(true);

        $languageManager->makePartial();

        $result = $languageManager->createNewLanguage($newLanguage);

        $this->assertTrue($result);
    }

    public function testWriteLanguageFile()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $fileName = 'test1';

        $inputs = array (
            'key1.key11' => 'value11',
            'key1.key12' => 'value12',
            'key2' => 'value2',
            'key3.key31' => 'value31',
            'key3.key32' => 'value32',
            'key4.key41.key42' => 'value42',
        );

        //TODO Add selection of safeMode once it is get from external conf file

        $languageManager
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('en');

        $languageManager
            ->shouldReceive('getLanguageFiles')
            ->atLeast()
            ->once()
            ->andReturn(array(
                0 => 'test1',
                1 => 'test2',
            ));

        $languageManager
            ->shouldReceive('getLanguageFile')
            ->with($fileName, true)
            ->atLeast()
            ->once()
            ->andReturn(array (
                'key1' =>
                    array (
                        'key11' => 'value11',
                        'key12' => 'value12',
                    ),
                'key2' => 'value2',
                'key3' =>
                    array (
                        'key31' => 'value31',
                        'key32' => 'value32',
                    ),
                'key4' =>
                    array (
                        'key41' => array (
                            'key42' => 'value42',
                        ),
                    ),
            ));

        $mockFileSystem
            ->shouldReceive('put')
            ->with(Mockery::type('string'), Mockery::any())
            ->once()
            ->andReturn(true);

        $languageManager->makePartial();

        $result = $languageManager->writeLanguageFile($fileName, $inputs);

        $this->assertTrue($result);
    }

    /**
     * @expectedException MicheleAngioni\MultiLanguage\Exceptions\TooManyNestedArraysException
     */
    public function testWriteLanguageFileTooManyNestedArrays()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $fileName = 'test1';

        $inputs = array (
            'key1.key11' => 'value11',
            'key1.key12' => 'value12',
            'key2' => 'value2',
            'key3.key31' => 'value31',
            'key3.key32.key321.key3211.key.32111' => 'value32111',
        );

        //TODO Add selection of safeMode once it is get from external conf file

        $languageManager
            ->shouldReceive('getLanguageFiles')
            ->atLeast()
            ->once()
            ->andReturn(array(
                0 => 'test1',
                1 => 'test2',
            ));

        $mockFileSystem
            ->shouldReceive('put')
            ->never();

        $languageManager->makePartial();

        $result = $languageManager->writeLanguageFile($fileName, $inputs);

        $this->assertTrue($result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWriteLanguageFileInvalidInputArray()
    {
        $mockFileSystem = $this->mock('MicheleAngioni\MultiLanguage\FileSystemInterface');
        $mockTranslator = $this->mock('Illuminate\Contracts\Translation\Translator');

        $languageManager = $this->getLanguageManagerMock($mockFileSystem, $mockTranslator);

        $fileName = 'test1';

        $inputs = array (
            'key1.key11' => array(
                'keyInsideArray' => 'valueInsideArray'
            ),
            'key1.key12' => 'value12',
            'key2' => 'value2',
            'key3.key31' => 'value31',
            'key3.key32' => 'value32111',
        );

        //TODO Add selection of safeMode once it is get from external conf file

        $languageManager
            ->shouldReceive('getLanguageFiles')
            ->atLeast()
            ->once()
            ->andReturn(array(
                0 => 'test1',
                1 => 'test2',
            ));

        $mockFileSystem
            ->shouldReceive('put')
            ->never();

        $languageManager->makePartial();

        $result = $languageManager->writeLanguageFile($fileName, $inputs);

        $this->assertTrue($result);
    }


    protected function getLanguageManagerMock($mockFileSystem, $mockTranslator)
    {
        $app = [];

        $app['config'] = $this->mock('config');
        $app['config']->shouldReceive('get')->with('ma_multilanguage.allowed_languages')->andReturn(10);
        $app['config']->shouldReceive('get')->with('ma_multilanguage.allowed_nested_arrays')->andReturn(3);
        $app['config']->shouldReceive('get')->with('app.fallback_locale')->andReturn('en');
        $app['config']->shouldReceive('get')->with('ma_multilanguage.max_text_length')->andReturn(100);
        $app['config']->shouldReceive('get')->with('ma_multilanguage.safe_mode')->andReturn(true);
        $app['config']->shouldReceive('get')->with('ma_multilanguage.language_files_path')->andReturn('/resources/lang');

        $app['path'] = '/app';

        $languageManager = \Mockery::mock('MicheleAngioni\MultiLanguage\LanguageManager',
            [$mockFileSystem, $mockTranslator, $app]);

        return $languageManager;
    }

    public function mock($class)
    {
        $mock = Mockery::mock($class);

        return $mock;
    }


    public function tearDown()
    {
        Mockery::close();
    }

}
