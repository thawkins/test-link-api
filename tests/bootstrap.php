<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Mock/ClientMock.php';

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

define('TMP_DIR', '/tmp/demo-app-tests');
Tester\Helpers::purge(TMP_DIR);