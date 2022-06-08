<?php

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RateLimiterTest extends TestCase {
    protected $storage = [
        'scheme' => 'tcp',
        'host' => 'redis',
        'port' => 6379
    ];

    public function setUp(): void {

    }

	public function testUnspecifiedCallback() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'throttle' => function() {}
        ]);
        $limiter();
    }

	public function testUnspecifiedThrottleCallback() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function() {}
        ]);
        $limiter();
    }

	public function testInvalidCallback() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function($request) {

            },
            'throttle' => 'invalid_callback'
        ], $this->storage);
        $request = (new Request)
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");
        $response = new Response;
        $limiter($request, $response, function() {});
    }

	public function testInvalidThrottle() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function($request) {
                return [
                    'key' => uniqid()
                ];
            },
            'throttle' => 'invalid_callback'
        ], $this->storage);
        $request = (new Request)
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");
        $response = new Response;
        $limiter($request, $response, function() {});
    }

	public function testMetaNotArray() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function($request) {
                return 'testing';
            },
            'throttle' => '',
        ], $this->storage);
        $request = (new Request)
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");
        $response = new Response;
        $limiter($request, $response, function() {});
    }

	public function testMetaDoesNotContainTokenKey() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function($request) {
                return [
                    'testing' => true
                ];
            },
            'throttle' => '',
        ], $this->storage);
        $request = (new Request)
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");
        $response = new Response;
        $limiter($request, $response, function() {});
    }


//    public function testDefaultHeader() {
//        $result = null;
//        $limiter = new LeakyBucketRateLimiter\RateLimiter([
//            'callback' => function($request) {
//                return [
//                    'token' => uniqid()
//                ];
//            },
//            'throttle' => ''
//        ], $this->storage);
//        $request = (new Request)
//            ->withUri(new Uri("https://example.com/api"))
//            ->withMethod("GET");
//        $response = new Response;
//        $limiter($request, $response, function($req, $res) {
//            $this->assertContains("X-Rate-Limit", array_keys($res->getHeaders()));
//        });
//    }
//
//    public function testCustomHeader() {
//        $result = null;
//        $limiter = new LeakyBucketRateLimiter\RateLimiter([
//            'callback' => function($request) {
//                return [
//                    'token' => uniqid()
//                ];
//            },
//            'throttle' => '',
//            'header' => 'X-Api-Rate-Limit'
//        ], $this->storage);
//        $request = (new Request)
//            ->withUri(new Uri("https://example.com/api"))
//            ->withMethod("GET");
//        $response = new Response;
//        $limiter($request, $response, function($req, $res) {
//            $this->assertContains("X-Api-Rate-Limit", array_keys($res->getHeaders()));
//        });
//    }
//
//    public function testDisabledHeader() {
//        $result = null;
//        $limiter = new LeakyBucketRateLimiter\RateLimiter([
//            'callback' => function($request) {
//                return [
//                    'token' => uniqid()
//                ];
//            },
//            'throttle' => '',
//            'header' => false
//        ], $this->storage);
//        $request = (new Request)
//            ->withUri(new Uri("https://example.com/api"))
//            ->withMethod("GET");
//        $response = new Response;
//        $limiter($request, $response, function($req, $res) {
//            $this->assertEmpty(array_keys($res->getHeaders()));
//        });
//    }
}
