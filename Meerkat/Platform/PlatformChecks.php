<?php

namespace Statamic\Addons\Meerkat\Platform;

class PlatformChecks
{

    const OPEN_CLASSROOMS_CLASS  = 'OpenClassrooms\\Akismet\\Client';
    const OPEN_CLASSROOMS_NAMESPACE = 'OpenClassrooms\\Akismet\\';

    protected $autoloader = null;

    public function __construct()
    {
        $this->autoloader = require statamic_path().'/vendor/autoload.php';
    }

    public function checkDependencies()
    {
        $platformPath = $this->getPlatformPath();      
        $openClassroomsExists = class_exists(self::OPEN_CLASSROOMS_CLASS);

        if (!$openClassroomsExists) {
            $openClassRoomsPath = $platformPath.'openclassrooms/akismet/src/';

            $this->autoloader->setPsr4(self::OPEN_CLASSROOMS_NAMESPACE, $openClassRoomsPath);
        }
    }

    public function getPlatformPath()
    {
        return __DIR__.'/files/';
    }

}