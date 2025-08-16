<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\ToyyibPay\Message\PurchaseRequest;
use Omnipay\ToyyibPay\Message\CompletePurchaseRequest;

/**
 * toyyibPay Payment Gateway
 *
 * toyyibPay is a Malaysian payment gateway that supports FPX (Financial Process Exchange)
 * and other local payment methods. This gateway provides a simple integration for
 * Malaysian businesses to accept online payments.
 *
 * Example:
 * <code>
 *   // Create a gateway for the toyyibPay
 *   $gateway = Omnipay::create('ToyyibPay');
 *   $gateway->setUserSecretKey('your-secret-key');
 *   $gateway->setCategoryCode('your-category-code');
 *   $gateway->setTestMode(true); // Set to false for production
 *
 *   // Create a purchase request
 *   $response = $gateway->purchase([
 *       'billName' => 'Test Bill',
 *       'billDescription' => 'Test Description',
 *       'billAmount' => '10.00',
 *       'billReturnUrl' => 'https://example.com/return',
 *       'billCallbackUrl' => 'https://example.com/callback',
 *       'billExternalReferenceNo' => 'REF123',
 *       'billTo' => 'John Doe',
 *       'billEmail' => 'john@example.com',
 *       'billPhone' => '0123456789'
 *   ])->send();
 *
 *   if ($response->isRedirect()) {
 *       $response->redirect();
 *   }
 * </code>
 *
 * @link https://toyyibpay.com/apireference/
 * @author Amirol Zolkifli <amirol@sitehandy.com>
 * @version 3.0
 */
class Gateway extends AbstractGateway
{
    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'toyyibPay';
    }

    /**
     * Get gateway default parameters
     *
     * @return array<string, mixed>
     */
    public function getDefaultParameters(): array
    {
        return [
            'testMode' => false,
            'userSecretKey' => '',
            'categoryCode' => '',
            'billPriceSetting' => 1, // 1 = Fixed Price
            'billPayorInfo' => 1, // 1 = Required
            'billSplitPayment' => 0, // 0 = Disabled
            'billPaymentChannel' => 0, // 0 = FPX
            'billDisplayMerchant' => 1, // 1 = Show merchant info
            'billChargeToCustomer' => 1 // 1 = Charge to customer
        ];
    }

    /**
     * Get the user secret key
     *
     * @return string|null
     */
    public function getUserSecretKey(): ?string
    {
        return $this->getParameter('userSecretKey');
    }

    /**
     * Set the user secret key
     *
     * @param string $value
     * @return $this
     */
    public function setUserSecretKey(string $value): self
    {
        return $this->setParameter('userSecretKey', $value);
    }

    /**
     * Get the category code
     *
     * @return string|null
     */
    public function getCategoryCode(): ?string
    {
        return $this->getParameter('categoryCode');
    }

    /**
     * Set the category code
     *
     * @param string $value
     * @return $this
     */
    public function setCategoryCode(string $value): self
    {
        return $this->setParameter('categoryCode', $value);
    }

    /**
     * Create a purchase request
     *
     * This will create a bill in toyyibPay system and return a redirect URL
     * for the customer to complete the payment.
     *
     * @param array<string, mixed> $parameters
     * @return RequestInterface
     */
    public function purchase(array $parameters = []): RequestInterface
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * Complete a purchase request
     *
     * This will verify the payment status after the customer returns from
     * the toyyibPay payment page.
     *
     * @param array<string, mixed> $parameters
     * @return RequestInterface
     */
    public function completePurchase(array $parameters = []): RequestInterface
    {
        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

    /**
     * Supports purchase transactions
     *
     * @return bool
     */
    public function supportsPurchase(): bool
    {
        return true;
    }

    /**
     * Supports complete purchase transactions
     *
     * @return bool
     */
    public function supportsCompletePurchase(): bool
    {
        return true;
    }
}
