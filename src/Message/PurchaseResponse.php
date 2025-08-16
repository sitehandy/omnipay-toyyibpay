<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * toyyibPay Create a Bill Response
 *
 * This response is returned when a bill creation request is made to toyyibPay.
 * The response will contain a BillCode and redirect URL if successful, which
 * should be used to redirect the customer to the payment page.
 *
 * @see PurchaseRequest
 * @link https://toyyibpay.com/apireference/
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param array<string, mixed> $data
     */
    public function __construct(RequestInterface $request, array $data)
    {
        parent::__construct($request, $data);
    }

    /**
     * Is the response successful?
     *
     * The initial server response is never complete without redirecting the user
     * to the payment gateway. A successful response means the bill was created
     * and the customer should be redirected to complete payment.
     *
     * @return bool Always false, as payment requires redirect
     */
    public function isSuccessful(): bool
    {
        return false;
    }

    /**
     * Does the response require a redirect?
     *
     * @return bool True if BillCode is present and redirect is required
     */
    public function isRedirect(): bool
    {
        return !empty($this->data['BillCode']) && !empty($this->data['redirectUrl']);
    }

    /**
     * Get the redirect URL
     *
     * @return string|null The URL to redirect to, or null if not available
     */
    public function getRedirectUrl(): ?string
    {
        return $this->data['redirectUrl'] ?? null;
    }

    /**
     * Get redirect data
     *
     * @return array<string, mixed> Empty array as all data is in the URL
     */
    public function getRedirectData(): array
    {
        return [];
    }

    /**
     * Get the redirect method
     *
     * @return string Always 'GET'
     */
    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    /**
     * Get the bill code
     *
     * @return string|null The bill code if available
     */
    public function getBillCode(): ?string
    {
        return $this->data['BillCode'] ?? null;
    }

    /**
     * Get the transaction reference
     *
     * This returns the bill code which can be used as a transaction reference
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        return $this->getBillCode();
    }

    /**
     * Get response message
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        if ($this->isRedirect()) {
            return 'Bill created successfully. Please redirect customer to payment page.';
        }
        
        return 'Sorry, there was an error creating your bill payment. Please try again later or contact administrator for further assistance.';
    }

    /**
     * Get response code
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->getBillCode();
    }

    /**
     * Is the response pending?
     *
     * @return bool True if redirect is required (payment pending)
     */
    public function isPending(): bool
    {
        return $this->isRedirect();
    }

    /**
     * Is the response cancelled?
     *
     * @return bool Always false for purchase responses
     */
    public function isCancelled(): bool
    {
        return false;
    }
}
