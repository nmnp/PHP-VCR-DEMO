<?php

use Httpful\Request;

class VcrDemoTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    /**
     * @author Naresh Maharjan <nareshmaharjan@lftechnology.com>
     * @vcr data.json
     */
    public function testMe()
    {
        $uri = "http://reqres.in/api/users";
        $response = Request::get($uri)->send();
        $this->assertEquals(1, $response->body->data[0]->id);
        $this->assertEquals('george', $response->body->data[0]->first_name);
        $this->assertEquals('bluth', $response->body->data[0]->last_name);
    }
}
