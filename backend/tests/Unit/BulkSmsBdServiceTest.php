<?php

namespace Tests\Unit;

use App\Services\BulkSmsBdService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BulkSmsBdServiceTest extends TestCase
{
    private BulkSmsBdService $sms;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'bulksmsbd.api_key'   => 'test_key',
            'bulksmsbd.sender_id' => 'TESTSENDER',
            'bulksmsbd.base_url'  => 'https://bulksmsbd.net/api',
        ]);
        $this->sms = new BulkSmsBdService();
    }

    public function test_send_returns_success_on_200(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response('{"response_code":202}', 200)]);

        $result = $this->sms->send('01718523171', 'Test OTP');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('202', $result['response']);
    }

    public function test_send_returns_failure_on_http_error(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response('Server Error', 500)]);

        $result = $this->sms->send('01718523171', 'Test OTP');

        $this->assertFalse($result['success']);
    }

    public function test_send_many_joins_numbers_with_comma(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response('{"response_code":202}', 200)]);

        $result = $this->sms->sendMany(['01718523171', '01612345678'], 'Broadcast');

        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'smsapi')
                && str_contains((string) ($request['number'] ?? ''), ',');
        });
    }

    public function test_send_bulk_different_posts_to_smsapimany(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response('{"response_code":202}', 200)]);

        $result = $this->sms->sendBulkDifferent([
            ['number' => '01718523171', 'message' => 'Hello Alice'],
            ['number' => '01612345678', 'message' => 'Hello Bob'],
        ]);

        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'smsapimany');
        });
    }

    public function test_format_number_strips_leading_zero(): void
    {
        $ref = new \ReflectionMethod(BulkSmsBdService::class, 'formatNumber');
        $ref->setAccessible(true);

        $this->assertSame('8801718523171', $ref->invoke($this->sms, '01718523171'));
    }

    public function test_format_number_strips_plus_prefix(): void
    {
        $ref = new \ReflectionMethod(BulkSmsBdService::class, 'formatNumber');
        $ref->setAccessible(true);

        $this->assertSame('8801718523171', $ref->invoke($this->sms, '+8801718523171'));
    }

    public function test_format_number_already_880_prefixed(): void
    {
        $ref = new \ReflectionMethod(BulkSmsBdService::class, 'formatNumber');
        $ref->setAccessible(true);

        $this->assertSame('8801718523171', $ref->invoke($this->sms, '8801718523171'));
    }

    public function test_format_number_without_leading_zero(): void
    {
        $ref = new \ReflectionMethod(BulkSmsBdService::class, 'formatNumber');
        $ref->setAccessible(true);

        $this->assertSame('8801718523171', $ref->invoke($this->sms, '1718523171'));
    }

    public function test_get_balance_returns_response(): void
    {
        Http::fake(['bulksmsbd.net/*' => Http::response('{"balance":100}', 200)]);

        $result = $this->sms->getBalance();

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('balance', $result['response']);
    }

    public function test_send_resilient_when_api_unreachable(): void
    {
        Http::fake([
            'bulksmsbd.net/*' => fn() => throw new \Illuminate\Http\Client\ConnectionException('Connection refused'),
        ]);

        $result = $this->sms->send('01718523171', 'Test');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Connection refused', $result['response']);
    }
}
