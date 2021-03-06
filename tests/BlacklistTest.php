<?php

namespace Tymon\JWTAuth\Test;

use Mockery;
use Tymon\JWTAuth\Blacklist;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Audience;
use Tymon\JWTAuth\Claims\Subject;
use Tymon\JWTAuth\Claims\JwtId;
use Tymon\JWTAuth\Claims\Custom;
use Illuminate\Support\Collection;

class BlacklistTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->storage = Mockery::mock('Tymon\JWTAuth\Contracts\Providers\Storage');
        $this->blacklist = new Blacklist($this->storage);

        $this->validator = Mockery::mock('Tymon\JWTAuth\Validators\PayloadValidator');
        $this->validator->shouldReceive('setRefreshFlow->check');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_add_a_valid_token_to_the_blacklist()
    {
        $claims = [
            'sub' => new Subject(1),
            'iss' => new Issuer('http://example.com'),
            'exp' => new Expiration(123 + 3600),
            'nbf' => new NotBefore(123),
            'iat' => new IssuedAt(123),
            'jti' => new JwtId('foo')
        ];
        $payload = new Payload(Collection::make($claims), $this->validator);

        $this->storage->shouldReceive('add')->with('foo', [], 61);
        $this->blacklist->add($payload);
    }

    /** @test */
    public function it_should_return_false_when_adding_an_expired_token_to_the_blacklist()
    {
        $claims = [
            'sub' => new Subject(1),
            'iss' => new Issuer('http://example.com'),
            'exp' => new Expiration(123 - 3600),
            'nbf' => new NotBefore(123),
            'iat' => new IssuedAt(123),
            'jti' => new JwtId('foo')
        ];
        $payload = new Payload(Collection::make($claims), $this->validator, true);

        $this->storage->shouldReceive('add')->never();
        $this->assertFalse($this->blacklist->add($payload));
    }

    /** @test */
    public function it_should_check_whether_a_token_has_been_blacklisted()
    {
        $claims = [
            'sub' => new Subject(1),
            'iss' => new Issuer('http://example.com'),
            'exp' => new Expiration(123 + 3600),
            'nbf' => new NotBefore(123),
            'iat' => new IssuedAt(123),
            'jti' => new JwtId('foobar')
        ];
        $payload = new Payload(Collection::make($claims), $this->validator);

        $this->storage->shouldReceive('has')->with('foobar')->andReturn(true);
        $this->storage->shouldReceive('get')->with('foobar')->andReturn(['valid_until' => 3723 + 0]);

        $this->assertTrue($this->blacklist->has($payload));
    }

    /** @test */
    public function it_should_remove_a_token_from_the_blacklist()
    {
        $claims = [
            'sub' => new Subject(1),
            'iss' => new Issuer('http://example.com'),
            'exp' => new Expiration(123 + 3600),
            'nbf' => new NotBefore(123),
            'iat' => new IssuedAt(123),
            'jti' => new JwtId('foobar')
        ];
        $payload = new Payload(Collection::make($claims), $this->validator);

        $this->storage->shouldReceive('destroy')->with('foobar')->andReturn(true);
        $this->assertTrue($this->blacklist->remove($payload));
    }

    /** @test */
    public function it_should_empty_the_blacklist()
    {
        $this->storage->shouldReceive('flush');
        $this->assertTrue($this->blacklist->clear());
    }
}
