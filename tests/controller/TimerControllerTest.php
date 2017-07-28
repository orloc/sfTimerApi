<?php

use Silex\WebTestCase;

if (!class_exists('\PHPUnit\Framework\TestCase', true)) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
} elseif (!class_exists('\PHPUnit_Framework_TestCase', true)) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}

class TimerControllerTest extends WebTestCase
{
    public function testBaseRoute()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        
        $client->request('GET', '/');
        
        
        $resp = $client->getResponse();
        $this->assertTrue($resp->isNotFound());
    }

    public function createApplication()
    {
        $_ENV['TEST_ENV'] = true;
        $app = require __DIR__ . '/../../app/app.php';
        $app['session.test'] = true;

        return $this->app = $app;
    }
}
