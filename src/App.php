<?php

namespace Mrcnpdlk\Toya;

use phpFastCache\CacheManager;

class App
{
    /**
     * class instance
     * @var object
     */
    protected static $_instance;
    /**
     * Getting class instance
     * @return object Class instance
     */
    public static function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new static;
        }
        return static::$_instance;
    }

    /**
     * Construct
     */
    protected function __construct()
    {
    }

    public function getObject_fileCache()
    {
        CacheManager::setDefaultConfig(array(
            "path"       => sys_get_temp_dir() . '/toya',
            'defaultTtl' => 900,
            'htaccess'   => false,
        ));

        $InstanceCache = CacheManager::getInstance('files');

        return $InstanceCache;
    }
    public function __get($name)
    {
        if (method_exists($this, 'getObject_' . $name)) {
            return $this->$name = $this->{'getObject_' . $name}();
        }
        throw new Exception\Error("Unknown property '{$name}'", 10);
    }
}
