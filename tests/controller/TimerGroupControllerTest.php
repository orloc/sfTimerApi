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
    private static $timer;
    
    public static function setUpBeforeClass(){
        $queries = [
            "delete from users_timer_groups;",
            "delete from users;",
            "delete from timer_groups;",
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

    public function testAllWithNone(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $client->request('GET', '/api/v1/timer-group', [], [], $headers);
        $resp = $client->getResponse();
        
        $this->assertTrue($resp->isSuccessful());
        
        $content = json_decode($resp->getContent());
        
        $this->assertTrue(empty($content));
    }

    public function testCreateBadFields(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();
        
        $nullId = [
            'name' => null
        ];

        $client->request('POST', '/api/v1/timer-group', [], [], $headers, json_encode($nullId));
        $resp = $client->getResponse();
        $this->assertTrue($resp->isClientError());
    }
    
    public function testCreatePositive(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $data = [
            'name' => 'groupone'
        ];

        $client->request('POST', '/api/v1/timer-group', [], [], $headers, json_encode($data));
        $resp = $client->getResponse();
        $this->assertTrue($resp->isSuccessful());
        
        $content = json_decode($resp->getContent(), true);
        
        $this->assertTrue(intval($content['created_by']) === intval(self::$user->getId()));
        $this->assertTrue($content['name'] === 'groupone');
        
        $q = self::$db->executeQuery("select * from users_timer_groups");
        $result = $q->fetchAll();
        
        $this->assertTrue(count($result) === 1);
        $item = array_pop($result);
        $this->assertTrue($item['user_id'] == self::$user->getId());
        $this->assertTrue($item['user_privilege'] === Entity\TimerGroup::PRIVILEGE_OWNER);
    }
    
    public function testGetNewTimerGroups(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $client->request('GET', '/api/v1/timer-group', [], [], $headers);
        $resp = $client->getResponse();
        $this->assertTrue($resp->isSuccessful());
        
        $content = json_decode($resp->getContent(), true);
        
        self::$timer = $content[0];
        $this->assertTrue(count($content) === 1);
    }
    
    public function testBadUpdate(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $data = [
            'name' => null
        ];

        $id = self::$timer['id'];

        $client->request('PATCH', "/api/v1/timer-group/{$id}", [], [], $headers, json_encode($data));
        $resp = $client->getResponse();
        
        $this->assertTrue($resp->isClientError());
    }
    
    public function testGoodUpdate(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $better = [
            'name' => 'grouponessss',
            'description' => null
        ];

        $id = self::$timer['id'];

        $client->request('PATCH', "/api/v1/timer-group/{$id}", [], [], $headers, json_encode($better));
        $resp = $client->getResponse();
        $this->assertTrue($resp->isSuccessful());
    }
    
    public function testBadGetTimerGroupMembers(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();

        $client->request('GET', "/api/v1/timer-group/member", [], [], $headers);
        $resp = $client->getResponse();
        $this->assertTrue($resp->isClientError());

        $client->request('GET', "/api/v1/timer-group/member?group_id=999999999", [], [], $headers);
        $resp = $client->getResponse();
        $this->assertTrue($resp->getStatusCode() === 401);
        
    }

    public function testGoodGetTimerGroupMembers(){
        $client = $this->createClient();
        $client->followRedirects(true);

        $headers = $this->getAuthHeaders();
        $id = self::$timer['id'];

        $client->request('GET', "/api/v1/timer-group/member?group_id={$id}", [], [], $headers);
        $resp = $client->getResponse();
        
        $this->assertTrue($resp->isSuccessful());
        $this->assertTrue(count(json_decode($resp->getContent(), true)) === 0);

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
