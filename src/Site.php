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

    public function getChannelsForPackage(string $packageName)
    {
        foreach ($this->getData() as $key => $value) {
            if ($value['packageName'] === $packageName) {
                return $value;
            }
        }

        return [];
    }

    public function getPackages()
    {
        $answer = [];
        foreach ($this->getData() as $key => $value) {
            $answer[] = $value['packageName'];
        }

        return $answer;
    }

    private function getData()
    {
        $key          = 'toya-channels';
        $CachedString = $this->oApp->fileCache->getItem($key);

        if ($CachedString->get() === null) {
            $CachedString->set($this->parseChannels())->expiresAfter(3600);
            $this->oApp->fileCache->save($CachedString);
        }
        $htmlContent = $CachedString->get();

        return $htmlContent;
    }

    /**
     * @param string|null $url
     *
     * @return mixed
     */
    private function getSiteContent(string $url = null)
    {
        if ($url === null) {
            $url = 'https://toya.net.pl/telewizja';
        }

        $key          = md5($url);
        $CachedString = $this->oApp->fileCache->getItem($key);

        if ($CachedString->get() === null) {
            $CachedString->set(file_get_contents($url))->expiresAfter(3600);
            $this->oApp->fileCache->save($CachedString);
        }
        $htmlContent = $CachedString->get();

        return $htmlContent;
    }

    private function parseChannels()
    {
        $answer     = [];
        $hdChannels = [];
        $dom        = new Dom;
        $dom->setOptions([
            'removeStyles'  => true,
            'removeScripts' => true,
        ]);
        $dom->load($this->getSiteContent());

        $offers = $dom->find('#main_body', 0)->find('.offers')->find('.offer');

        foreach ($offers as $offer) {
            $packageName        = trim($offer->find('.offer-title')->innerHtml);
            $channelsArray     = [];
            $channalesRealArray = [];
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

                $tmp = [
                    'id'   => $channelId,
                    'name' => $channelName,
                    'isHd' => $isHd,
                ];

                $channelsArray[] = $tmp;
                if ($isHd) {
                    $hdChannels[] = $tmp;
                }
            }

            //szukamy czy kanal ma swoj odpowiednik w HD
            foreach ($channelsArray as $key => $ch) {
                $channelsArray[$key]['betterQuality'] = null;
                foreach ($hdChannels as $hd) {
                    if (preg_match('/' . preg_quote($ch['name']) . '\s+HD/', $hd['name'])) {
                        $channelsArray[$key]['betterQuality'] = $hd;
                        continue;
                    }
                }
                if (!isset($channelsArray[$key]['betterQuality'])) {
                    $channalesRealArray[] = $ch;
                }
            }

            $answer[] = [
                'packageName'       => $packageName,
                'channelsAllCount'  => count($channelsArray),
                'channelsAll'       => $channelsArray,
                'channelsRealCount' => count($channalesRealArray),
                'channelsReal'      => $channalesRealArray,
                'channelsHdCount'   => count($hdChannels),
            ];
        }

        return $answer;
    }
}
