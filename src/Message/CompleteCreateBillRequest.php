<?php

namespace Omnipay\ToyyibPay\Message;

class CreateBillRequest extends AbstractRequest
{
    protected $productionEndpoint = 'https://' . $this->baseEndpoint . 'index.php/api/getBillTransactions';

    protected $sandboxEndpoint = 'https://dev.' . $this->baseEndpoint . 'index.php/api/getBillTransactions';

    public function getData()
    {
        $data = array(
            'billCode' => $this->getBillCode(),
            'billpaymentStatus' => $this->getBillPaymentStatus(),
          );
    }

    public function getBillCode()
    {
        return $this->getParameter('billCode');
    }

    public function setBillCode($value)
    {
        return $this->setParameter('billCode', $value);
    }

    public function getBillPaymentStatus()
    {
        return $this->getParameter('billpaymentStatus');
    }

    public function setBillPaymentStatus($value)
    {
        return $this->setParameter('billpaymentStatus', $value);
    }
    
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request('POST', $this->getEndpoint(), [], http_build_query($data, '', '&'));

        return $this->createResponse($httpResponse->getBody()->getContents());
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->sandboxEndpoint : $this->productionEndpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new CompleteCreateBillResponse($this, $data);
    }

}