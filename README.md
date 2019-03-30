# toya-channel-parser
Pobieranie informacji o kanałach w danym pakiecie TV

```php
use Monolog\Handler\ErrorLogHandler;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

require __DIR__ . '/../vendor/autoload.php';

$option = new ConfigurationOption([
    'path'       => sys_get_temp_dir() . '/toya',
    'defaultTtl' => 900,
]);
$cache  = CacheManager::getInstance('files', $option);


$logger = new \Monolog\Logger('toya-parser');
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, \Psr\Log\LogLevel::DEBUG));

$site = new \Mrcnpdlk\Toya\Site(new \Mrcnpdlk\Toya\Config([
    'cache'  => $cache,
    'logger' => $logger,
]));
var_dump($site->getPackages());
print_r($site->getChannelsForPackage('WYGODNY'));
```
