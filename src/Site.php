<?php

namespace Mrcnpdlk\Toya;

use Mrcnpdlk\Toya\Model\ChannelModel;
use Mrcnpdlk\Toya\Model\ChannelResponseModel;
use PHPHtmlParser\Dom;

class Site
{

    /**
     * @var \Mrcnpdlk\Toya\Config
     */
    protected $oConfig;

    public function __construct(Config $config)
    {
        $this->oConfig = $config;
    }

    /**
     * @param string $packageName
     *
     * @return array|mixed
     * @throws \Mrcnpdlk\Lib\ModelMapException
     * @throws \Mrcnpdlk\Toya\Exception
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    public function getChannelsForPackage(string $packageName)
    {
        foreach ($this->getData() as $key => $value) {
            if ($value->pkgName === $packageName) {
                return $value;
            }
        }

        return [];
    }

    /**
     * @return array
     * @throws \Mrcnpdlk\Lib\ModelMapException
     * @throws \Mrcnpdlk\Toya\Exception
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    public function getPackages(): array
    {
        $answer = [];
        foreach ($this->getData() as $key => $value) {
            $answer[] = $value->pkgName;
        }

        return $answer;
    }

    /**
     * @return ChannelResponseModel[]
     * @throws \Mrcnpdlk\Lib\ModelMapException
     * @throws \Mrcnpdlk\Toya\Exception
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    private function getData(): array
    {
        $key          = 'toya-channels';
        $CachedString = $this->oConfig->getCache()->getItem($key);

        if ($CachedString->get() === null) {
            $CachedString->set($this->parseChannels())->expiresAfter(3600);
            $this->oConfig->getCache()->save($CachedString);
        }
        /** @var array $htmlContent */
        $htmlContent = $CachedString->get();

        return $htmlContent;
    }

    /**
     * @return string|null
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    private function getSiteContent(): ?string
    {
        $key          = md5($this->oConfig->getUrl());
        $CachedString = $this->oConfig->getCache()->getItem($key);

        if ($CachedString->get() === null) {
            $CachedString->set(file_get_contents($this->oConfig->getUrl()))
                         ->expiresAfter(3600)
            ;
            $this->oConfig
                ->getCache()
                ->save($CachedString)
            ;
        }
        /** @var string|null $htmlContent */
        $htmlContent = $CachedString->get();

        return $htmlContent;
    }

    /**
     * @return ChannelResponseModel[]
     * @throws \Mrcnpdlk\Lib\ModelMapException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     * @throws \Mrcnpdlk\Toya\Exception
     */
    private function parseChannels(): array
    {
        /** @var ChannelResponseModel[] $answer */
        $answer = [];
        /** @var ChannelModel[] $hdChannels */
        $hdChannels = [];
        $dom        = new Dom;
        $dom->setOptions([
            'removeStyles'  => true,
            'removeScripts' => true,
        ]);
        $dom->load($this->getSiteContent());

        $offers = $dom->find('#main_body', 0)->find('.offers')->find('.offer');

        /** @var Dom[] $offers */
        foreach ($offers as $offer) {
            $packageName = trim($offer->find('.offer-title')->innerHtml);
            /** @var ChannelModel[] $channelsArray */
            $channelsArray = [];
            /** @var ChannelModel[] $channelsRealArray */
            $channelsRealArray = [];
            $channels          = $offer->find('.offer-more-content-hidden')->find('.offer-more-channel-wrapper')->find('.offer-more-channel');

            /** @var Dom[] $channels */
            foreach ($channels as $channel) {
                $channelName = trim($channel->find('.channel-tooltip')->find('img')->getAttribute('alt'));
                $desc        = strip_tags($channel->find('.channel-tooltip-content')->innerHtml);

                if (preg_match('/.*kanale: (\\d+)/', $desc, $matches)) {
                    $channelId = $matches[1];
                } else {
                    throw new Exception("Not found channel ID for {$channelName}", 1);
                }

                $isHd = preg_match('/HD$/', $channelName) ? true : false;

                /** @var ChannelModel $chModel */
                $chModel = $this
                    ->oConfig
                    ->getMapper()
                    ->jsonMap(ChannelModel::class, [
                        'number' => $channelId,
                        'name'   => $channelName,
                        'isHd'   => $isHd,
                    ])
                ;

                $channelsArray[] = $chModel;
                if ($isHd) {
                    $hdChannels[] = $chModel;
                }
            }

            //szukamy czy kanal ma swoj odpowiednik w HD
            foreach ($channelsArray as $key => $ch) {
                $channelsArray[$key]->betterQuality = null;
                foreach ($hdChannels as $hd) {
                    if (preg_match('/' . preg_quote($ch->name) . '\s+HD/', $hd->name)) {
                        $channelsArray[$key]->betterQuality = $hd;
                        continue;
                    }
                }
                if (!isset($channelsArray[$key]->betterQuality)) {
                    $channelsRealArray[] = $ch;
                }
            }

            $answer[] = $this->oConfig->getMapper()->jsonMap(ChannelResponseModel::class, [
                'pkgName'      => $packageName,
                'allCount'     => count($channelsArray),
                'channels'     => $channelsArray,
                'realCount'    => count($channelsRealArray),
                'channelsReal' => $channelsRealArray,
                'hdCount'      => count($hdChannels),
            ])
            ;

        }
        
        return $answer;
    }
}
