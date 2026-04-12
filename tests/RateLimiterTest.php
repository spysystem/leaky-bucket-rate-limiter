<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

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
		new LeakyBucketRateLimiter\RateLimiter([
            'throttle' => function() {}
        ]);
    }

	public function testUnspecifiedThrottleCallback() {
		$this->expectException(InvalidArgumentException::class);
		new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function() {}
        ]);
    }

	public function testInvalidCallback() {
		$this->expectException(InvalidArgumentException::class);
		$limiter = new LeakyBucketRateLimiter\RateLimiter([
            'callback' => function($request) {

            },
            'throttle' => 'invalid_callback'
        ], $this->storage);
        $request = new Request('GET', new Uri("https://example.com/api"));
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
        $request = new Request('GET', new Uri("https://example.com/api"));
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
        $request = new Request('GET', new Uri("https://example.com/api"));
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
        $request = new Request('GET', new Uri("https://example.com/api"));
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
//        $request = new Request('GET', new Uri("https://example.com/api"));
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
//        $request = new Request('GET', new Uri("https://example.com/api"));
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
//        $request = new Request('GET', new Uri("https://example.com/api"));
//        $response = new Response;
//        $limiter($request, $response, function($req, $res) {
//            $this->assertEmpty(array_keys($res->getHeaders()));
//        });
//    }
}
