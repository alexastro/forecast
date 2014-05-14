#!/usr/bin/env php
<?php

require_once __DIR__.'/app/autoload.php';
require_once __DIR__.'/app/AppKernel.php';
use Forecast\WeatherBundle\Services\NotificationCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;


$kernel = new \AppKernel('prod', false);
$kernel->loadClassCache();
$application = new Application($kernel);
$application->add(new NotificationCommand());
$application->run();