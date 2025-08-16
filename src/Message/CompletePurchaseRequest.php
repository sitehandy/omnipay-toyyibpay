<?php

declare(strict_types=1);

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * toyyibPay Complete Purchase Request
 *
 * This request is used to verify the payment status after the customer
 * returns from the toyyibPay payment page. It retrieves the transaction
 * details and payment status for a specific bill.
 *
 * Example:
 * <code>
 *   $request = $gateway->completePurchase([
 *       'billCode' => 'abc123def456',
 *       'billpaymentStatus' => 1 // Optional filter
 *   ]);
 *   $response = $request->send();
 *   
 *   if ($response->isSuccessful()) {
 *       // Payment was successful
 *       $transactionId = $response->getTransactionId();
 *       $transactionReference = $response->getTransactionReference();
 *   }
 * </code>
 *
 * @link https://toyyibpay.com/apireference/
 */
class CompletePurchaseRequest extends AbstractRequest
{
    /**
     * API endpoint for getting bill transactions
     */
    protected string $apiEndpoint = 'index.php/api/getBillTransactions';

    /**
     * Validate required parameters for completing purchase
     *
     * @throws InvalidRequestException
     */
    protected function validateParameters(): void
    {
        // Validate required parameters
        $this->validateRequired('billCode');
        
        // Validate bill code format (should be alphanumeric)
        $billCode = $this->getBillCode();
        if ($billCode !== null && !preg_match('/^[a-zA-Z0-9]+$/', $billCode)) {
            throw new InvalidRequestException('Invalid bill code format');
        }
        
        // Validate payment status if provided
        $paymentStatus = $this->getBillPaymentStatus();
        if ($paymentStatus !== null && !in_array($paymentStatus, [1, 2, 3, 4], true)) {
            throw new InvalidRequestException('billpaymentStatus must be 1 (Successful), 2 (Pending), 3 (Unsuccessful), or 4 (Pending)');
        }
    }

    /**
     * Create response instance
     *
     * @param array<string, mixed> $data
     * @return CompletePurchaseResponse
     */
    protected function createResponse(array $data): CompletePurchaseResponse
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
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
            'billCode' => $this->getBillCode()
        ];
        
        // Add optional payment status filter if provided
        $paymentStatus = $this->getBillPaymentStatus();
        if ($paymentStatus !== null) {
            $data['billpaymentStatus'] = $paymentStatus;
        }

        return $data;
    }

    /**
     * Send the request with specified data
     *
     * @param array<string, mixed> $data
     * @return CompletePurchaseResponse
     * @throws InvalidRequestException
     */
    public function sendData($data): CompletePurchaseResponse
    {
        $data['apiEndpoint'] = $this->apiEndpoint;
        
        try {
            $httpResponse = $this->sendRequest($data);
            
            // Validate response structure
            if (!is_array($httpResponse) || empty($httpResponse)) {
                throw new InvalidRequestException('Invalid response from toyyibPay API');
            }
            
            $firstResponse = $httpResponse[0] ?? null;
            if (!is_array($firstResponse)) {
                throw new InvalidRequestException('Invalid transaction data in API response');
            }
            
            // Add the bill URL to the response data
            $responseData = $firstResponse;
            $responseData['billUrl'] = $this->getEndpoint() . ($data['billCode'] ?? '');
            
            return $this->createResponse($responseData);
            
        } catch (\Exception $e) {
            throw new InvalidRequestException('Failed to get bill transaction: ' . $e->getMessage(), 0, $e);
        }
    }
}
