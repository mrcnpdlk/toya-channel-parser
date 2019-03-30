<?php
/**
 * Created by Marcin.
 * Date: 30.03.2019
 * Time: 20:48
 */

namespace Mrcnpdlk\Toya\Model;


class ChannelModel
{
    /**
     * @var int
     */
    public $number;
    /**
     * @var string
     */
    public $name;
    /**
     * @var bool
     */
    public $isHd;
    /**
     * @var \Mrcnpdlk\Toya\Model\ChannelModel|null
     */
    public $betterQuality;
}
