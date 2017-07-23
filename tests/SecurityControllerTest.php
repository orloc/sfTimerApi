<?php

use Silex\WebTestCase;

if (!class_exists('\PHPUnit\Framework\TestCase', true)) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
} elseif (!class_exists('\PHPUnit_Framework_TestCase', true)) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}

class SecurityControllerTest extends WebTestCase
{
    public function testNullLogin() {
        $client = $this->createClient();
        
        $client->request('POST', '/login');
        $resp = $client->getResponse();
        $this->assertTrue($resp->isClientError());
    }

    public function testBadUserLogin() {
        $client = $this->createClient();

        $content = [
            'username' => 'bad',
            'password' => ''
        ];
        
        $client->request('POST', '/login', [],[],[
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($content));
        
        $resp = $client->getResponse();
        $this->assertTrue($resp->isNotFound());
    }

    public function testBadPasswordLogin() {
        $client = $this->createClient();

        $content = [
            'username' => 'orloc',
            'password' => ''
        ];

        $client->request('POST', '/login', [],[],[
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($content));

        $resp = $client->getResponse();
        $this->assertTrue($resp->isClientError());
    }
    
    public function testGoodLogin(){
        $client = $this->createClient();

        $content = [
            'username' => 'orloc',
            'password' => 'password'
        ];

        $client->request('POST', '/login', [],[],[
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($content));

        $resp = $client->getResponse();
        try {
            $json = json_decode($resp->getContent(), true);
            $this->assertTrue(isset($json['token']));
            $this->assertTrue(isset($json['requested']));
        } catch(\Exception $e){
            throw $e;
        }
    }

    public function createApplication()
    {
        $_ENV['TEST_ENV'] = true;
        $app = require __DIR__ . '/../app/app.php';
        $app['session.test'] = true;

        return $this->app = $app;
    }
}
