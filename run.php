<?php

require __DIR__ . '/vendor/autoload.php';

use phpFastCache\CacheManager;
use PHPHtmlParser\Dom;

$htmlContent = null;

CacheManager::setDefaultConfig(array(
    "path" => __DIR__ . '/cache', // or in windows "C:/tmp/"
));

$InstanceCache = CacheManager::getInstance('files');

$key          = "toya-site";
$CachedString = $InstanceCache->getItem($key);

if (is_null($CachedString->get())) {
    $CachedString->set(file_get_contents('https://toya.net.pl/telewizja'))->expiresAfter(60);
    $InstanceCache->save($CachedString);
    $htmlContent = $CachedString->get();

} else {
    $htmlContent = $CachedString->get();
}

$dom = new Dom;
$dom->setOptions([
    'removeStyles'  => true,
    'removeScripts' => true,
]);
$dom->load($htmlContent);
//$html = $dom->outerHtml;

//var_dump($html);

$offers = $dom->find('#main_body', 0)->find('.offers')->find('.offer');
echo count($offers);
foreach ($offers as $offer) {
    var_dump($offer->find('.offer-title')->innerHtml);

    $channels = $offer->find('.offer-more-content-hidden')->find('.offer-more-channel-wrapper')->find('.offer-more-channel');

    foreach ($channels as $channel) {
        echo "\n" . $channel->find('.channel-tooltip')->find('img')->getAttribute('alt') . "\n";

        $desc = strip_tags($channel->find('.channel-tooltip-content')->innerHtml);

        if (preg_match('/.*kanale: (\\d+)/', $desc, $matches)) {
            //print_r($matches);
            echo $matches[1];
        }
    }

}
