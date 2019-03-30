<?php
/**
 * Created by Marcin.
 * Date: 30.03.2019
 * Time: 21:47
 */

namespace Mrcnpdlk\Toya\Model;


class ChannelResponseModel
{
    /**
     * @var string
     */
    public $pkgName;
    /**
     * @var int
     */
    public $allCount;
    /**
     * @var \Mrcnpdlk\Toya\Model\ChannelModel[]
     */
    public $channels;
    /**
     * @var int
     */
    public $realCount;
    /**
     * @var \Mrcnpdlk\Toya\Model\ChannelModel[]
     */
    public $channelsReal;
    /**
     * @var int
     */
    public $hdCount;
}
