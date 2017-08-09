<?php

namespace EQT\tests\controller;

use Silex\WebTestCase;
use \EQT\Api\Utility;
use \EQT\Api\Entity;

if (!class_exists('\PHPUnit\Framework\TestCase', true)) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
} elseif (!class_exists('\PHPUnit_Framework_TestCase', true)) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}

class UserControllerTest extends WebTestCase
{
    
    private static $db;
    private static $user;
    
    public static function setUpBeforeClass(){
        $queries = [
            "delete from users;",
            "insert into users (username, email, password, created_at, last_login, roles, type, profile_name)
             values (
                'orloc', 
                'grant.tepper@gmail.com', 
                '$2y$13\$P7E7qlroxr5LaLltZq7QoODh5TVLXyQsQ3sW7iIClBv.NAX23o1/2', 
                NOW(), 
                null, 
                'ROLE_MEMBER', 
                'REGISTERED',
                'profile name')"
        ];
        

        $_ENV['TEST_ENV'] = true;
        $app = require __DIR__ . '/../../app/app.php';
        $app['session.test'] = true;


        foreach ($queries as $q){
            $app['db']->executeQuery($q);
        }

        self::$db = $app['db'];
        self::$user = Utility::mapRequest(self::$db->fetchAll('select * from users limit 1')[0], $app['eqt.models.user']);
    }

    public function testGetMeNoAuth() {
        $client = $this->createClient();
        $client->followRedirects(true);

        $client->request('GET', '/api/v1/user/me', [], []);
        $resp = $client->getResponse();
        $this->assertTrue($resp->getStatusCode() === 401);
    }
    
    public function testGetMe() {
        $client = $this->createClient();
        $client->followRedirects(true);
        
        $headers = $this->getAuthHeaders();

        $client->request('GET', '/api/v1/user/me', [], [], $headers);
        $resp = $client->getResponse();
        
        $this->assertTrue($resp->isSuccessful());
        $content = json_decode($resp->getContent(), true);
        $this->assertTrue($content['id'] === self::$user->getId());
        $this->assertTrue(!isset($content['password']));
    }
    
    public function testUserUpdateNoAuth(){
        $client = $this->createClient();
        $client->followRedirects(true);
        $client->request('PATCH', '/api/v1/user', [], []);
        
        $resp = $client->getResponse();
        $this->assertTrue($resp->getStatusCode() === 401);
    }

    public function testUserUpdateNullId() {
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $nullId = [
            'id' => null, 
            'email' => 'things@stuff.com',
            'profile_name' => 'meow'
        ];
        $client->request('PATCH', '/api/v1/user', [], [], $headers, json_encode($nullId));
        $this->assertTrue($client->getResponse()->getStatusCode() === 401);
    }

    public function testUserUpdateNoExist() {
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();
        $nonExistantId = [
            'id' => 33,
            'email' => 'things@stuff.com',
            'profile_name' => 'meow'
        ];
        
        $client->request('PATCH', '/api/v1/user', [], [], $headers, json_encode($nonExistantId));
        $this->assertTrue($client->getResponse()->getStatusCode() === 401);
    }

    public function testUserUpdateMissingId() {
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $missingId = [
            'email' => 'things@stuff.com',
            'profile_name' => 'meow'
        ];

        $client->request('PATCH', '/api/v1/user', [], [], $headers, json_encode($missingId));
        $this->assertTrue($client->getResponse()->getStatusCode() === 401);
    }

    public function testUserUpdateBadEmail() {
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $invalidFields = [
            'id' => self::$user->getId(),
            'profile_name' => 'meow',
            'email' => 'meow.com'
        ];

        $client->request('PATCH', '/api/v1/user', [], [], $headers, json_encode($invalidFields));
        $this->assertTrue($client->getResponse()->isClientError());
    }

    public function testUserUpdate() {
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();
        
        $missingFields = [
            'id' => self::$user->getId(),
            'profile_name' => 'meow',
            'email' => 'meow@woof.com'
        ];

        $client->request('PATCH', '/api/v1/user', [], [], $headers, json_encode($missingFields));
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function getAuthHeaders(){
        $encoder = $this->app['eqt.jwt_encoder'];
        $token = $encoder->encode(self::$user);
        return [
            'HTTP_x-eqtaccess-token' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/json'
        ];
    }


    public function createApplication()
    {
        $_ENV['TEST_ENV'] = true;
        $app = require __DIR__ . '/../../app/app.php';
        $app['session.test'] = true;

        return $this->app = $app;
    }
}
