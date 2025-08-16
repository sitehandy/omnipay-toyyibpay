<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * toyyibPay Complete Purchase Response
 *
 * This response is returned when checking the payment status of a bill.
 * It contains the transaction details and payment status information.
 *
 * @see CompletePurchaseRequest
 * @link https://toyyibpay.com/apireference/
 */
class CompletePurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Payment status constants
     */
    public const STATUS_SUCCESSFUL = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_UNSUCCESSFUL = 3;
    public const STATUS_PENDING_ALT = 4;

    /**
     * Get payment status description
     *
     * @param int|null $value Payment status code
     * @return string Status description
     */
    private static function getPaymentStatusDescription(?int $value): string
    {
        switch ($value) {
            case self::STATUS_SUCCESSFUL:
                return 'Successful transaction';
            case self::STATUS_PENDING:
                return 'Pending transaction';
            case self::STATUS_UNSUCCESSFUL:
                return 'Unsuccessful transaction';
            case self::STATUS_PENDING_ALT:
                return 'Pending';
            default:
                return 'Unknown payment status. Please contact administrator for assistance.';
        }
    }

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
     * Is the transaction successful?
     *
     * @return bool True if payment status is successful
     */
    public function isSuccessful(): bool
    {
        $status = $this->getPaymentStatus();
        return $status === self::STATUS_SUCCESSFUL;
    }

    /**
     * Get the transaction reference
     *
     * @return string|null The bill permalink
     */
    public function getTransactionReference(): ?string
    {
        return $this->data['billPermalink'] ?? null;
    }

    /**
     * Get the transaction ID
     *
     * @return string|null The payment invoice number
     */
    public function getTransactionId(): ?string
    {
        return $this->data['billpaymentInvoiceNo'] ?? null;
    }

    /**
     * Get the payment status code
     *
     * @return int|null The payment status code
     */
    public function getPaymentStatus(): ?int
    {
        $status = $this->data['billpaymentStatus'] ?? null;
        return $status !== null ? (int)$status : null;
    }

    /**
     * Is the transaction pending?
     *
     * @return bool True if payment status is pending
     */
    public function isPending(): bool
    {
        $status = $this->getPaymentStatus();
        return in_array($status, [self::STATUS_PENDING, self::STATUS_PENDING_ALT], true);
    }

    /**
     * Is the transaction cancelled/unsuccessful?
     *
     * @return bool True if payment status is unsuccessful
     */
    public function isCancelled(): bool
    {
        $status = $this->getPaymentStatus();
        return $status === self::STATUS_UNSUCCESSFUL;
    }

    /**
     * Does the response require a redirect?
     *
     * Only redirect if the status is not successful (i.e., pending or unsuccessful)
     *
     * @return bool True if redirect is required
     */
    public function isRedirect(): bool
    {
        return !$this->isSuccessful() && !empty($this->data['billUrl']);
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
     * Get the redirect URL
     *
     * @return string|null The bill URL if available
     */
    public function getRedirectUrl(): ?string
    {
        return $this->data['billUrl'] ?? null;
    }

    /**
     * Get the bill code
     *
     * @return string|null The bill code if available
     */
    public function getBillCode(): ?string
    {
        return $this->data['billCode'] ?? null;
    }

    /**
     * Get the bill amount
     *
     * @return string|null The bill amount if available
     */
    public function getAmount(): ?string
    {
        return $this->data['billAmount'] ?? null;
    }

    /**
     * Get the payment date
     *
     * @return string|null The payment date if available
     */
    public function getPaymentDate(): ?string
    {
        return $this->data['billpaymentDate'] ?? null;
    }

    /**
     * Get the payer name
     *
     * @return string|null The payer name if available
     */
    public function getPayerName(): ?string
    {
        return $this->data['billTo'] ?? null;
    }

    /**
     * Get the payer email
     *
     * @return string|null The payer email if available
     */
    public function getPayerEmail(): ?string
    {
        return $this->data['billEmail'] ?? null;
    }

    /**
     * Get response message
     *
     * @return string Status message based on payment status
     */
    public function getMessage(): string
    {
        $status = $this->getPaymentStatus();
        
        if ($status !== null) {
            return self::getPaymentStatusDescription($status);
        }
        
        return 'Bill code is not valid. Please try again later or contact administrator for further assistance.';
    }

    /**
     * Get response code
     *
     * @return string|null The payment status code as string
     */
    public function getCode(): ?string
    {
        $status = $this->getPaymentStatus();
        return $status !== null ? (string)$status : null;
    }

    /**
     * Get all transaction data
     *
     * @return array<string, mixed> All available transaction data
     */
    public function getData(): array
    {
        return $this->data;
    }
}
