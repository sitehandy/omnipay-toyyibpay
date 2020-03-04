<?php

namespace Omnipay\ToyyibPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * toyyibPay Create a Bill Response
 */
class CompletePurchaseResponse extends AbstractResponse implements RedirectResponseInterface
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
        return isset($this->data['billpaymentStatus']) && $this->data['billpaymentStatus'] == 1 ? true : false;
    }

    public function getTransactionReference()
    {
        return $this->data['billPermalink'];
    }

    public function getTransactionId()
    {
        return $this->data['billpaymentInvoiceNo'];
    }

    /**
     * Only redirect if the status is not 1 = Successful Transaction.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return isset($this->data['billpaymentStatus']) && $this->data['billpaymentStatus'] != 1 ? true : false;
    }

    /**
     * @return array empy array; all the data is in the GET URL
     */
    public function getRedirectData()
    {
        return [];
    }

    /**
     * @return string Always GET
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * @return string|null URL if present
     */
    public function getRedirectUrl()
    {
        return isset($this->data['billUrl']) ? $this->data['billUrl'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['billpaymentStatus']) ? self::paymentStatus($this->data['billpaymentStatus']) : 'Bill code is not valid. Please try again later or contact administrator for further assistance.';
    }
}
