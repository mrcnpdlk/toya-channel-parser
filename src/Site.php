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
            $packageName        = $offer->find('.offer-title')->innerHtml;
            $channalesArray     = array();
            $channalesRealArray = array();
            $channels           = $offer->find('.offer-more-content-hidden')->find('.offer-more-channel-wrapper')->find('.offer-more-channel');

            foreach ($channels as $channel) {
                $channelName = trim($channel->find('.channel-tooltip')->find('img')->getAttribute('alt'));
                $desc        = strip_tags($channel->find('.channel-tooltip-content')->innerHtml);

                if (preg_match('/.*kanale: (\\d+)/', $desc, $matches)) {
                    $channelId = $matches[1];
                } else {
                    throw new Exception\Error("Not found channel ID for {$channelName}", 1);
                }

                $isHd = preg_match('/HD$/', $channelName) ? true : false;

                $channalesArray[] = array(
                    'id'   => $channelId,
                    'name' => $channelName,
                    'isHd' => $isHd,
                );
            }

            usort($channalesArray, function ($item1, $item2) {
                return strtolower($item1['name']) <=> strtolower($item2['name']);
            });

            foreach ($channalesArray as $key => $pos) {
                if (isset($channalesArray[$key + 1]) && preg_match("/{$pos['name']} HD/", $channalesArray[$key + 1]['name'])) {
                    $channalesArray[$key]['betterQuality'] = $channalesArray[$key + 1];
                } else {
                    $channalesArray[$key]['betterQuality'] = null;
                    $channalesRealArray[]                  = $channalesArray[$key];
                }
            }

            $answer[] = array(
                'packageName'       => $packageName,
                'channelsAllCount'  => count($channalesArray),
                'channelsAll'       => $channalesArray,
                'channelsRealCount' => count($channalesRealArray),
                'channelsReal'      => $channalesRealArray,
            );
        }
        return $answer;
    }
    public function getChannels()
    {
        $key          = 'toya-channels';
        $CachedString = $this->oApp->fileCache->getItem($key);

        if (is_null($CachedString->get())) {
            $CachedString->set($this->parseChannels())->expiresAfter(1);
            $this->oApp->fileCache->save($CachedString);
            $htmlContent = $CachedString->get();

        } else {
            $htmlContent = $CachedString->get();
        }

        return $htmlContent;
    }
}
