<?php

namespace Omnipay\ToyyibPay\Message;

/**
 * toyyibPay Get a Bill Transaction
 */
class CompletePurchaseRequest extends AbstractRequest
{
    protected $apiEndpoint = 'index.php/api/getBillTransactions';

    protected function guardParameters()
    {
        $this->validate(
            'billCode'
        );
    }

    protected function createResponse($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    public function getData()
    {
        $this->guardParameters();

        $data = array(
            'billCode' => $this->getBillCode(),
            'billpaymentStatus' => $this->getBillpaymentStatus()
        );

        return $data;
    }

    public function sendData($data)
    {
        $data['apiEndpoint'] = $this->apiEndpoint;
        $httpResponse = $this->sendRequest($data);
        
        $dataResponse = $httpResponse[0];
        $dataResponse['billUrl'] =  $this->getEndpoint() . $data['billCode'];

        return $this->createResponse($dataResponse);
    }
}
