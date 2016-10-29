<?php
namespace Mrcnpdlk\Toya;

require __DIR__ . '/vendor/autoload.php';
$c = new Site();

$channels = $c->getChannels();

print_r($channels);
