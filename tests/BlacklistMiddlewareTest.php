<?php

namespace Orkhanahmadov\LaravelIpMiddleware\Tests;

use Illuminate\Http\Request;
use Orkhanahmadov\LaravelIpMiddleware\BlacklistMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Orkhanahmadov\LaravelIpMiddleware\WhitelistMiddleware;

class BlacklistMiddlewareTest extends TestCase
{
    /**
     * @var WhitelistMiddleware
     */
    private $middleware;

    public function testBlocksIfIpIsBlacklist()
    {
        $this->expectException(HttpException::class);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);

        $this->middleware->handle($request, function () {
        }, '1.1.1.1', '2.2.2.2');
    }

    public function testBlocksWithCloudflareIpAddress()
    {
        $this->expectException(HttpException::class);
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_CF_CONNECTING_IP' => '2.1.1.1']);

        $this->middleware->handle($request, function () {
            return true;
        }, '2.1.1.1');
    }

    public function testAllowsIfIpIsNotBlocklisted()
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '2.1.1.1']);

        $result = $this->middleware->handle($request, function () {
            return true;
        }, '1.1.1.1');

        $this->assertTrue($result);
    }

    public function testAllowsIfEnvironmentIsIgnored()
    {
        app()['config']->set('ip-middleware.ignore_environments', ['testing']);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);

        $result = $this->middleware->handle($request, function () {
            return true;
        }, '1.1.1.1');

        $this->assertTrue($result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = $this->app->make(BlacklistMiddleware::class);
    }
}