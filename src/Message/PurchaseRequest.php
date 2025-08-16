<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * toyyibPay Create a Bill Request
 *
 * This request creates a new bill in the toyyibPay system. The bill can then be
 * paid by customers through various payment channels including FPX, credit cards,
 * and e-wallets.
 *
 * Example:
 * <code>
 *   $request = $gateway->purchase([
 *       'billName' => 'Test Bill',
 *       'billDescription' => 'Test Description',
 *       'billAmount' => '10.00',
 *       'billReturnUrl' => 'https://example.com/return',
 *       'billCallbackUrl' => 'https://example.com/callback',
 *       'billExternalReferenceNo' => 'REF123',
 *       'billTo' => 'John Doe',
 *       'billEmail' => 'john@example.com',
 *       'billPhone' => '0123456789'
 *   ]);
 *   $response = $request->send();
 * </code>
 *
 * @link https://toyyibpay.com/apireference/
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * API endpoint for creating bills
     */
    protected string $apiEndpoint = 'index.php/api/createBill';

    /**
     * Validate required parameters for bill creation
     *
     * @throws InvalidRequestException
     */
    protected function validateParameters(): void
    {
        // Validate required parameters
        $this->validateRequired(
            'userSecretKey',
            'categoryCode',
            'billName',
            'billDescription',
            'billAmount',
            'billReturnUrl',
            'billCallbackUrl',
            'billExternalReferenceNo',
            'billTo',
            'billEmail',
            'billPhone'
        );

        // Validate email format
        $this->validateEmail($this->getBillEmail());
        
        // Validate URLs
        $this->validateUrl($this->getBillReturnUrl());
        $this->validateUrl($this->getBillCallbackUrl());
        
        // Validate amount
        $this->validateAmount($this->getParameter('billAmount'));
        
        // Validate bill price setting
        $billPriceSetting = $this->getBillPriceSetting();
        if ($billPriceSetting !== null && !in_array($billPriceSetting, [1, 2], true)) {
            throw new InvalidRequestException('billPriceSetting must be 1 (Fixed Price) or 2 (Open Price)');
        }
        
        // Validate bill payor info
        $billPayorInfo = $this->getBillPayorInfo();
        if ($billPayorInfo !== null && !in_array($billPayorInfo, [0, 1], true)) {
            throw new InvalidRequestException('billPayorInfo must be 0 (Optional) or 1 (Required)');
        }
        
        // Validate payment channel
        $paymentChannel = $this->getBillPaymentChannel();
        if ($paymentChannel !== null && !in_array($paymentChannel, [0, 1, 2], true)) {
            throw new InvalidRequestException('billPaymentChannel must be 0 (FPX), 1 (Credit Card), or 2 (Boost)');
        }
    }

    /**
     * Create response instance
     *
     * @param array<string, mixed> $data
     * @return PurchaseResponse
     */
    protected function createResponse(array $data): PurchaseResponse
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * Get the data for this request
     *
     * @return array<string, mixed>
     * @throws InvalidRequestException
     */
    public function getData(): array
    {
        $this->validateParameters();

        $data = [
            'userSecretKey' => $this->getUserSecretKey(),
            'categoryCode' => $this->getCategoryCode(),
            'billName' => $this->getBillName(),
            'billDescription' => $this->getBillDescription(),
            'billPriceSetting' => $this->getBillPriceSetting() ?? 1, // Default to Fixed Price
            'billPayorInfo' => $this->getBillPayorInfo() ?? 1, // Default to Required
            'billAmount' => $this->getBillAmount(),
            'billReturnUrl' => $this->getBillReturnUrl(),
            'billCallbackUrl' => $this->getBillCallbackUrl(),
            'billExternalReferenceNo' => $this->getBillExternalReferenceNo(),
            'billTo' => $this->getBillTo(),
            'billEmail' => $this->getBillEmail(),
            'billPhone' => $this->getBillPhone(),
            'billSplitPayment' => $this->getBillSplitPayment() ?? 0, // Default to Disabled
            'billPaymentChannel' => $this->getBillPaymentChannel() ?? 0, // Default to FPX
            'billDisplayMerchant' => $this->getBillDisplayMerchant() ?? 1, // Default to Show
            'billChargeToCustomer' => $this->getBillChargeToCustomer() ?? 1 // Default to Charge Customer
        ];

        // Add optional parameters if they are set
        if ($this->getBillSplitPaymentArgs() !== null) {
            $data['billSplitPaymentArgs'] = $this->getBillSplitPaymentArgs();
        }
        
        if ($this->getBillContentEmail() !== null) {
            $data['billContentEmail'] = $this->getBillContentEmail();
        }
        
        if ($this->getBillAdditionalField() !== null) {
            $data['billAdditionalField'] = $this->getBillAdditionalField();
        }

        // Remove null values to keep the request clean
        return array_filter($data, static function ($value) {
            return $value !== null;
        });
    }

    /**
     * Send the request with specified data
     *
     * @param array<string, mixed> $data
     * @return PurchaseResponse
     * @throws InvalidRequestException
     */
    public function sendData($data): PurchaseResponse
    {
        $data['apiEndpoint'] = $this->apiEndpoint;
        
        try {
            $httpResponse = $this->sendRequest($data);
            
            // Validate response structure
            if (!is_array($httpResponse) || empty($httpResponse)) {
                throw new InvalidRequestException('Invalid response from toyyibPay API');
            }
            
            $firstResponse = $httpResponse[0] ?? null;
            if (!is_array($firstResponse) || !isset($firstResponse['BillCode'])) {
                throw new InvalidRequestException('Missing BillCode in API response');
            }
            
            $responseData = [
                'BillCode' => $firstResponse['BillCode'],
                'redirectUrl' => $this->getEndpoint() . $firstResponse['BillCode']
            ];
            
            return $this->createResponse($responseData);
            
        } catch (\Exception $e) {
            throw new InvalidRequestException('Failed to create bill: ' . $e->getMessage(), 0, $e);
        }
    }
}
