<?php

namespace Mrcnpdlk\Toya;

use PHPHtmlParser\Dom;

class Site
{

    protected $oApp;

    public function __construct()
    {
        $this->oApp = App::getInstance();
    }

    private function getSiteContent(string $url = null)
    {
        if ($url === null) {
            $url = 'https://toya.net.pl/telewizja';
        }

        $key          = $url;
        $CachedString = $this->oApp->fileCache->getItem($key);

        if (is_null($CachedString->get())) {
            $CachedString->set(file_get_contents($url))->expiresAfter(3600);
            $this->oApp->fileCache->save($CachedString);
            $htmlContent = $CachedString->get();

        } else {
            $htmlContent = $CachedString->get();
        }

        return $htmlContent;
    }

    private function parseChannels()
    {
        $answer = array();
        $dom    = new Dom;
        $dom->setOptions([
            'removeStyles'  => true,
            'removeScripts' => true,
        ]);
        $dom->load($this->getSiteContent());

        $offers = $dom->find('#main_body', 0)->find('.offers')->find('.offer');

        foreach ($offers as $offer) {
            $chennalesArray = array();
            $channels       = $offer->find('.offer-more-content-hidden')->find('.offer-more-channel-wrapper')->find('.offer-more-channel');

            foreach ($channels as $channel) {
                $channelName = trim($channel->find('.channel-tooltip')->find('img')->getAttribute('alt'));
                $desc        = strip_tags($channel->find('.channel-tooltip-content')->innerHtml);

                if (preg_match('/.*kanale: (\\d+)/', $desc, $matches)) {
                    $channelId = $matches[1];
                } else {
                    throw new Exception\Error("Not found channel ID for {$channelName}", 1);
                }
                $chennalesArray[] = array(
                    'id'   => $channelId,
                    'name' => $channelName,
                );
            }
            $answer[] = array(
                'packageName'   => $offer->find('.offer-title')->innerHtml,
                'channelsCount' => count($chennalesArray),
                'channels'      => $chennalesArray,
            );
        }
        return $answer;
    }
    public function getChannels()
    {
        $key          = 'toya-channels';
        $CachedString = $this->oApp->fileCache->getItem($key);

        if (is_null($CachedString->get())) {
            $CachedString->set($this->parseChannels())->expiresAfter(60);
            $this->oApp->fileCache->save($CachedString);
            $htmlContent = $CachedString->get();

        } else {
            $htmlContent = $CachedString->get();
        }

        return $htmlContent;
    }
}
