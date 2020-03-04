<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * toyyibPay Create a Bill Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    private static function paymentStatus($value)
    {
        switch ($value) {
            case 1:
                $status = 'Successful transaction';
                break;
            case 2:
                $status = 'Pending transaction';
                break;
            case 3:
                $status = 'Unsuccessful transaction';
                break;
            case 4:
                $status = 'Pending';
                break;
            default:
                $status = 'Sorry, there was an error in getting your Bill Transactions. Please try again later or contact administrator for further assistance.';
        }

        return $status;
    }

    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    public function isSuccessful()
    {
        return $this->data['billpaymentStatus'] == 1 ? true : false;
    }

    public function getTransactionReference()
    {
        return $this->data['billPermalink'];
    }

    public function getTransactionId()
    {
        return $this->data['billpaymentInvoiceNo'];
    }

    public function getMessage()
    {
        return self::paymentStatus($this->data['billpaymentStatus']);
    }
}
