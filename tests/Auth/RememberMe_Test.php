<?php
namespace tests\Auth;

use tests\Auth\mocks\RememberMock;
use tests\Auth\mocks\SimpleUserList;
use WScore\Auth\Auth;
use WScore\Auth\RememberCookie;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class RememberMe_Test extends \PHPUnit_Framework_TestCase
{
    var $idList = array();

    /**
     * @var SimpleUserList
     */
    var $user;

    /**
     * @var Auth
     */
    var $auth;

    var $session = array();

    var $user_save_id;

    /**
     * @var array
     */
    var $remembered = [];

    /**
     * @var RememberMock
     */
    var $rememberMe;

    /**
     * @var array
     */
    var $cookie = [];
    
    var $cookie_saved = [];

    function setup()
    {
        $this->idList       = array(
            'test' => 'test-PW',
            'more' => 'more-PW',
        );
        $this->remembered = array(
            'remember' => 'its-me',
        );
        $this->user         = new SimpleUserList($this->idList);
        $this->user_save_id = 'auth-' . str_replace('\\', '-', get_class($this->user));
        
        $this->rememberMe   = new RememberMock();
        $this->cookie       = new RememberCookie($this->cookie);
        $this->cookie->setSetCookie([$this, 'setCookie']);
        
        $this->auth         = new Auth($this->user, $this->rememberMe, $this->cookie);
        $this->auth->setSession($this->session);
    }
    
    function setCookie($name, $value, $time, $path, $secure)
    {
        $this->cookie_saved[] = compact('name', 'value', 'time', 'path', 'secure');
    }

    function test0()
    {
        $this->assertEquals('tests\Auth\mocks\SimpleUserList', get_class($this->user));
        $this->assertEquals('tests\Auth\mocks\RememberMock', get_class($this->rememberMe));
        $this->assertEquals('WScore\Auth\Auth', get_class($this->auth));
        $this->assertEquals('WScore\Auth\RememberCookie', get_class($this->cookie));
        $this->assertEquals('tests\Auth\mocks\SimpleUserList', get_class($this->auth->getUser()));
    }

    /**
     * @test
     */
    function login_with_rememberMeFlag_saves_remembered_data()
    {
        $this->assertEmpty($this->cookie_saved);
        $authOK = $this->auth->login('test', 'test-PW', true);
        // test auth status
        $this->assertEquals( true, $authOK );
        $this->assertEquals( true, $this->auth->isLogin() );
        
        // test that 'test' is saved in RememberMock.
        $this->assertArrayHasKey('test', $this->rememberMe->remembered);
        $this->assertEquals('token-test', $this->rememberMe->remembered['test']);
        
        // test that id & token are saved in cookies.
        $this->assertNotEmpty($this->cookie_saved);
        $savedCookie = $this->cookie_saved[0];
        $this->assertEquals('remember-id', $savedCookie['name']);
        $this->assertEquals('test', $savedCookie['value']);

        $savedCookie = $this->cookie_saved[1];
        $this->assertEquals('remember-me', $savedCookie['name']);
        $this->assertEquals('token-test', $savedCookie['value']);
    }

}