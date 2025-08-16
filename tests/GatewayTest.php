<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay\Tests;

use PHPUnit\Framework\TestCase;
use Omnipay\ToyyibPay\Gateway;
use Omnipay\ToyyibPay\Message\PurchaseRequest;
use Omnipay\ToyyibPay\Message\CompletePurchaseRequest;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * toyyibPay Gateway Test
 *
 * @group toyyibpay
 */
class GatewayTest extends TestCase
{
    /**
     * @var Gateway
     */
    protected Gateway $gateway;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = new Client();
        $httpRequest = new Request();
        
        $this->gateway = new Gateway($httpClient, $httpRequest);
        $this->gateway->setUserSecretKey('test-secret-key');
        $this->gateway->setCategoryCode('test-category');
        $this->gateway->setTestMode(true);
    }

    /**
     * Test gateway name
     */
    public function testGetName(): void
    {
        $this->assertSame('toyyibPay', $this->gateway->getName());
    }

    /**
     * Test default parameters
     */
    public function testGetDefaultParameters(): void
    {
        $parameters = $this->gateway->getDefaultParameters();
        
        $this->assertIsArray($parameters);
        $this->assertArrayHasKey('testMode', $parameters);
        $this->assertArrayHasKey('userSecretKey', $parameters);
        $this->assertArrayHasKey('categoryCode', $parameters);
        $this->assertFalse($parameters['testMode']);
    }

    /**
     * Test user secret key getter and setter
     */
    public function testUserSecretKey(): void
    {
        $this->gateway->setUserSecretKey('new-secret-key');
        $this->assertSame('new-secret-key', $this->gateway->getUserSecretKey());
    }

    /**
     * Test category code getter and setter
     */
    public function testCategoryCode(): void
    {
        $this->gateway->setCategoryCode('new-category');
        $this->assertSame('new-category', $this->gateway->getCategoryCode());
    }

    /**
     * Test purchase request creation
     */
    public function testPurchase(): void
    {
        $request = $this->gateway->purchase([
            'billName' => 'Test Bill',
            'billDescription' => 'Test Description',
            'billAmount' => '10.00',
            'billReturnUrl' => 'https://example.com/return',
            'billCallbackUrl' => 'https://example.com/callback',
            'billExternalReferenceNo' => 'REF123',
            'billTo' => 'John Doe',
            'billEmail' => 'john@example.com',
            'billPhone' => '0123456789'
        ]);

        $this->assertInstanceOf(PurchaseRequest::class, $request);
        $this->assertSame('Test Bill', $request->getBillName());
        $this->assertSame('Test Description', $request->getBillDescription());
        $this->assertSame(1000, $request->getBillAmount()); // Amount in cents
        $this->assertSame('https://example.com/return', $request->getBillReturnUrl());
        $this->assertSame('https://example.com/callback', $request->getBillCallbackUrl());
        $this->assertSame('REF123', $request->getBillExternalReferenceNo());
        $this->assertSame('John Doe', $request->getBillTo());
        $this->assertSame('john@example.com', $request->getBillEmail());
        $this->assertSame('0123456789', $request->getBillPhone());
    }

    /**
     * Test complete purchase request creation
     */
    public function testCompletePurchase(): void
    {
        $request = $this->gateway->completePurchase([
            'billCode' => 'abc123def456'
        ]);

        $this->assertInstanceOf(CompletePurchaseRequest::class, $request);
        $this->assertSame('abc123def456', $request->getBillCode());
    }

    /**
     * Test supports purchase
     */
    public function testSupportsPurchase(): void
    {
        $this->assertTrue($this->gateway->supportsPurchase());
    }

    /**
     * Test supports complete purchase
     */
    public function testSupportsCompletePurchase(): void
    {
        $this->assertTrue($this->gateway->supportsCompletePurchase());
    }

    /**
     * Test purchase with missing required parameters
     */
    public function testPurchaseWithMissingParameters(): void
    {
        $this->expectException(InvalidRequestException::class);
        
        $request = $this->gateway->purchase([
            'billName' => 'Test Bill'
            // Missing required parameters
        ]);
        
        $request->getData(); // This should trigger validation
    }

    /**
     * Test complete purchase with missing bill code
     */
    public function testCompletePurchaseWithMissingBillCode(): void
    {
        $this->expectException(InvalidRequestException::class);
        
        $request = $this->gateway->completePurchase([]);
        $request->getData(); // This should trigger validation
    }

    /**
     * Test purchase with invalid email
     */
    public function testPurchaseWithInvalidEmail(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        $request = $this->gateway->purchase([
            'billName' => 'Test Bill',
            'billDescription' => 'Test Description',
            'billAmount' => '10.00',
            'billReturnUrl' => 'https://example.com/return',
            'billCallbackUrl' => 'https://example.com/callback',
            'billExternalReferenceNo' => 'REF123',
            'billTo' => 'John Doe',
            'billEmail' => 'invalid-email',
            'billPhone' => '0123456789'
        ]);
        
        $request->getData();
    }

    /**
     * Test purchase with invalid URL
     */
    public function testPurchaseWithInvalidUrl(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid URL format');
        
        $request = $this->gateway->purchase([
            'billName' => 'Test Bill',
            'billDescription' => 'Test Description',
            'billAmount' => '10.00',
            'billReturnUrl' => 'invalid-url',
            'billCallbackUrl' => 'https://example.com/callback',
            'billExternalReferenceNo' => 'REF123',
            'billTo' => 'John Doe',
            'billEmail' => 'john@example.com',
            'billPhone' => '0123456789'
        ]);
        
        $request->getData();
    }

    /**
     * Test purchase with invalid amount
     */
    public function testPurchaseWithInvalidAmount(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Amount must be a positive number');
        
        $request = $this->gateway->purchase([
            'billName' => 'Test Bill',
            'billDescription' => 'Test Description',
            'billAmount' => '-10.00',
            'billReturnUrl' => 'https://example.com/return',
            'billCallbackUrl' => 'https://example.com/callback',
            'billExternalReferenceNo' => 'REF123',
            'billTo' => 'John Doe',
            'billEmail' => 'john@example.com',
            'billPhone' => '0123456789'
        ]);
        
        $request->getData();
    }
}