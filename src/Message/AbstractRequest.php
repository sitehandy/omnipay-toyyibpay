<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\RuntimeException;

/**
 * toyyibPay Abstract Request
 *
 * This is the parent class for all toyyibPay requests.
 *
 * @see \Omnipay\ToyyibPay\Gateway
 * @link https://toyyibpay.com/apireference/
 */
abstract class AbstractRequest extends BaseAbstractRequest
{
    /**
     * Production endpoint URL
     */
    protected string $productionEndpoint = 'https://toyyibpay.com/';

    /**
     * Sandbox endpoint URL
     */
    protected string $sandboxEndpoint = 'https://dev.toyyibpay.com/';

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
     * Get the bill name
     *
     * @return string|null
     */
    public function getBillName(): ?string
    {
        return $this->getParameter('billName');
    }

    /**
     * Set the bill name
     *
     * @param string $value
     * @return $this
     */
    public function setBillName(string $value): self
    {
        return $this->setParameter('billName', $value);
    }

    /**
     * Get the bill description
     *
     * @return string|null
     */
    public function getBillDescription(): ?string
    {
        return $this->getParameter('billDescription');
    }

    /**
     * Set the bill description
     *
     * @param string $value
     * @return $this
     */
    public function setBillDescription(string $value): self
    {
        return $this->setParameter('billDescription', $value);
    }

    /**
     * Get the bill price setting
     *
     * @return int|null
     */
    public function getBillPriceSetting(): ?int
    {
        return $this->getParameter('billPriceSetting');
    }

    /**
     * Set the bill price setting
     *
     * @param int $value 1 = Fixed Price, 2 = Open Price
     * @return $this
     */
    public function setBillPriceSetting(int $value): self
    {
        return $this->setParameter('billPriceSetting', $value);
    }

    /**
     * Get the bill payor info setting
     *
     * @return int|null
     */
    public function getBillPayorInfo(): ?int
    {
        return $this->getParameter('billPayorInfo');
    }

    /**
     * Set the bill payor info setting
     *
     * @param int $value 1 = Required, 0 = Optional
     * @return $this
     */
    public function setBillPayorInfo(int $value): self
    {
        return $this->setParameter('billPayorInfo', $value);
    }

    /**
     * Get the bill amount in cents
     *
     * Note: toyyibPay expects amount in cents, so we multiply by 100
     *
     * @return int|null
     */
    public function getBillAmount(): ?int
    {
        $amount = $this->getParameter('billAmount');
        return $amount !== null ? (int)($amount * 100) : null;
    }

    /**
     * Set the bill amount
     *
     * @param string|float|int $value Amount in ringgit (will be converted to cents)
     * @return $this
     */
    public function setBillAmount($value): self
    {
        return $this->setParameter('billAmount', $value);
    }

    /**
     * Get the bill return URL
     *
     * @return string|null
     */
    public function getBillReturnUrl(): ?string
    {
        return $this->getParameter('billReturnUrl');
    }

    /**
     * Set the bill return URL
     *
     * @param string $value
     * @return $this
     */
    public function setBillReturnUrl(string $value): self
    {
        return $this->setParameter('billReturnUrl', $value);
    }

    /**
     * Get the bill callback URL
     *
     * @return string|null
     */
    public function getBillCallbackUrl(): ?string
    {
        return $this->getParameter('billCallbackUrl');
    }

    /**
     * Set the bill callback URL
     *
     * @param string $value
     * @return $this
     */
    public function setBillCallbackUrl(string $value): self
    {
        return $this->setParameter('billCallbackUrl', $value);
    }

    /**
     * Get the bill external reference number
     *
     * @return string|null
     */
    public function getBillExternalReferenceNo(): ?string
    {
        return $this->getParameter('billExternalReferenceNo');
    }

    /**
     * Set the bill external reference number
     *
     * @param string $value
     * @return $this
     */
    public function setBillExternalReferenceNo(string $value): self
    {
        return $this->setParameter('billExternalReferenceNo', $value);
    }

    /**
     * Get the bill recipient name
     *
     * @return string|null
     */
    public function getBillTo(): ?string
    {
        return $this->getParameter('billTo');
    }

    /**
     * Set the bill recipient name
     *
     * @param string $value
     * @return $this
     */
    public function setBillTo(string $value): self
    {
        return $this->setParameter('billTo', $value);
    }

    /**
     * Get the bill email
     *
     * @return string|null
     */
    public function getBillEmail(): ?string
    {
        return $this->getParameter('billEmail');
    }

    /**
     * Set the bill email
     *
     * @param string $value
     * @return $this
     */
    public function setBillEmail(string $value): self
    {
        return $this->setParameter('billEmail', $value);
    }

    /**
     * Get the bill phone number
     *
     * @return string|null
     */
    public function getBillPhone(): ?string
    {
        return $this->getParameter('billPhone');
    }

    /**
     * Set the bill phone number
     *
     * @param string $value
     * @return $this
     */
    public function setBillPhone(string $value): self
    {
        return $this->setParameter('billPhone', $value);
    }

    /**
     * Get the bill split payment setting
     *
     * @return int|null
     */
    public function getBillSplitPayment(): ?int
    {
        return $this->getParameter('billSplitPayment');
    }

    /**
     * Set the bill split payment setting
     *
     * @param int $value 0 = Disabled, 1 = Enabled
     * @return $this
     */
    public function setBillSplitPayment(int $value): self
    {
        return $this->setParameter('billSplitPayment', $value);
    }

    /**
     * Get the bill split payment arguments
     *
     * @return string|null
     */
    public function getBillSplitPaymentArgs(): ?string
    {
        return $this->getParameter('billSplitPaymentArgs');
    }

    /**
     * Set the bill split payment arguments
     *
     * @param string $value
     * @return $this
     */
    public function setBillSplitPaymentArgs(string $value): self
    {
        return $this->setParameter('billSplitPaymentArgs', $value);
    }

    /**
     * Get the bill payment channel
     *
     * @return int|null
     */
    public function getBillPaymentChannel(): ?int
    {
        return $this->getParameter('billPaymentChannel');
    }

    /**
     * Set the bill payment channel
     *
     * @param int $value 0 = FPX, 1 = Credit Card, 2 = Boost
     * @return $this
     */
    public function setBillPaymentChannel(int $value): self
    {
        return $this->setParameter('billPaymentChannel', $value);
    }

    /**
     * Get the bill display merchant setting
     *
     * @return int|null
     */
    public function getBillDisplayMerchant(): ?int
    {
        return $this->getParameter('billDisplayMerchant');
    }

    /**
     * Set the bill display merchant setting
     *
     * @param int $value 1 = Show merchant info, 0 = Hide merchant info
     * @return $this
     */
    public function setBillDisplayMerchant(int $value): self
    {
        return $this->setParameter('billDisplayMerchant', $value);
    }

    /**
     * Get the bill content email
     *
     * @return string|null
     */
    public function getBillContentEmail(): ?string
    {
        return $this->getParameter('billContentEmail');
    }

    /**
     * Set the bill content email
     *
     * @param string $value
     * @return $this
     */
    public function setBillContentEmail(string $value): self
    {
        return $this->setParameter('billContentEmail', $value);
    }

    /**
     * Get the bill additional field
     *
     * @return string|null
     */
    public function getBillAdditionalField(): ?string
    {
        return $this->getParameter('billAdditionalField');
    }

    /**
     * Set the bill additional field
     *
     * @param array<string, mixed>|string $value
     * @return $this
     */
    public function setBillAdditionalField($value): self
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        }
        return $this->setParameter('billAdditionalField', $value);
    }

    /**
     * Get the bill charge to customer setting
     *
     * @return int|null
     */
    public function getBillChargeToCustomer(): ?int
    {
        return $this->getParameter('billChargeToCustomer');
    }

    /**
     * Set the bill charge to customer setting
     *
     * @param int $value 1 = Charge to customer, 0 = Charge to merchant
     * @return $this
     */
    public function setBillChargeToCustomer(int $value): self
    {
        return $this->setParameter('billChargeToCustomer', $value);
    }

    /**
     * Get the bill code
     *
     * @return string|null
     */
    public function getBillCode(): ?string
    {
        return $this->getParameter('billCode');
    }

    /**
     * Set the bill code
     *
     * @param string $value
     * @return $this
     */
    public function setBillCode(string $value): self
    {
        return $this->setParameter('billCode', $value);
    }

    /**
     * Get the bill payment status
     *
     * @return int|null
     */
    public function getBillPaymentStatus(): ?int
    {
        return $this->getParameter('billpaymentStatus');
    }

    /**
     * Set the bill payment status
     *
     * @param int $value
     * @return $this
     */
    public function setBillPaymentStatus(int $value): self
    {
        return $this->setParameter('billpaymentStatus', $value);
    }

    /**
     * Send request to toyyibPay API
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function sendRequest(array $data): array
    {
        $apiEndpoint = $data['apiEndpoint'] ?? '';
        unset($data['apiEndpoint']);

        $url = $this->getEndpoint() . $apiEndpoint;
        
        try {
            $httpResponse = $this->httpClient->request(
                $this->getHttpMethod(),
                $url,
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'User-Agent' => 'Omnipay/ToyyibPay v3.0'
                ],
                http_build_query($data)
            );

            $body = $httpResponse->getBody()->getContents();
            
            if (empty($body)) {
                throw new RuntimeException('Empty response from toyyibPay API');
            }

            $response = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON response from toyyibPay API: ' . json_last_error_msg());
            }

            if (!is_array($response)) {
                throw new RuntimeException('Unexpected response format from toyyibPay API');
            }

            return $response;
            
        } catch (\Exception $e) {
            throw new RuntimeException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get HTTP method for the request
     *
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'POST';
    }

    /**
     * Get the API endpoint URL
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->getTestMode() ? $this->sandboxEndpoint : $this->productionEndpoint;
    }

    /**
     * Validate required parameters
     *
     * @param string ...$parameters
     * @throws InvalidRequestException
     */
    protected function validateRequired(string ...$parameters): void
    {
        foreach ($parameters as $parameter) {
            $value = $this->getParameter($parameter);
            if (empty($value)) {
                throw new InvalidRequestException("The {$parameter} parameter is required");
            }
        }
    }

    /**
     * Validate email format
     *
     * @param string|null $email
     * @throws InvalidRequestException
     */
    protected function validateEmail(?string $email): void
    {
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidRequestException('Invalid email format');
        }
    }

    /**
     * Validate URL format
     *
     * @param string|null $url
     * @throws InvalidRequestException
     */
    protected function validateUrl(?string $url): void
    {
        if ($url !== null && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidRequestException('Invalid URL format');
        }
    }

    /**
     * Validate amount
     *
     * @param mixed $amount
     * @throws InvalidRequestException
     */
    protected function validateAmount($amount): void
    {
        if ($amount !== null && (!is_numeric($amount) || $amount <= 0)) {
            throw new InvalidRequestException('Amount must be a positive number');
        }
    }
}
