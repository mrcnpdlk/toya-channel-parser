<?php

namespace Mrcnpdlk\Toya;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

class App
{
    /**
     * class instance
     *
     * @var object
     */
    protected static $_instance;

    /**
     * Construct
     */
    protected function __construct()
    {
    }

    /**
     * Getting class instance
     *
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
     * @param $name
     *
     * @return mixed
     * @throws \Mrcnpdlk\Toya\Exception\Error
     */
    public function __get($name)
    {
        if (method_exists($this, 'getObject_' . $name)) {
            return $this->$name = $this->{'getObject_' . $name}();
        }
        throw new Exception\Error("Unknown property '{$name}'", 10);
    }

    /**
     * @return \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
     * @throws \ReflectionException
     */
    public function getObject_fileCache(): \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
    {
        $option        = new ConfigurationOption([
            'path'       => sys_get_temp_dir() . '/toya',
            'defaultTtl' => 900,
        ]);

        return CacheManager::getInstance('files', $option);
    }
}
