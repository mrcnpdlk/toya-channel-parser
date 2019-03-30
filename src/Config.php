<?php
/**
 * Created by Marcin.
 * Date: 30.03.2019
 * Time: 20:29
 */

namespace Mrcnpdlk\Toya;


use Mrcnpdlk\Lib\ConfigurationOptionsAbstract;
use Mrcnpdlk\Lib\Mapper;
use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;

/**
 * Class Config
 *
 * @package Mrcnpdlk\Toya
 */
class Config extends ConfigurationOptionsAbstract
{
    /**
     * @var \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     */
    protected $cache;
    /**
     * @var \Mrcnpdlk\Lib\Mapper
     */
    protected $mapper;
    /**
     * @var string
     */
    protected $url = 'https://toya.net.pl/telewizja';

    /**
     * Config constructor.
     *
     * @param array $config
     *
     * @throws \Mrcnpdlk\Lib\ConfigurationException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
     */
    public function __construct(array $config = [])
    {
        $this->mapper = new Mapper(null, $config['logger'] ?? null);
        $this->cache  = CacheManager::getInstance('devnull');
        parent::__construct($config);
    }

    /**
     * @return \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     */
    public function getCache(): ExtendedCacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * @return \Mrcnpdlk\Lib\Mapper
     */
    public function getMapper(): \Mrcnpdlk\Lib\Mapper
    {
        return $this->mapper;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface $cache
     *
     * @return $this
     */
    public function setCache(ExtendedCacheItemPoolInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @param \Mrcnpdlk\Lib\Mapper $mapper
     *
     * @return Config
     */
    public function setMapper(\Mrcnpdlk\Lib\Mapper $mapper): Config
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return Config
     */
    public function setUrl(string $url): Config
    {
        $this->url = $url;

        return $this;
    }

}
