<?php

namespace Omnipay\ToyyibPay\Message;

class CompleteCreateBillRequest extends AbstractRequest
{
    protected $productionEndpoint = 'https://toyyibpay.com/';

    protected $sandboxEndpoint = 'https://dev.toyyibpay.com/';

    protected $apiEndpoint = 'index.php/api/getBillTransactions';

    protected function guardParameters()
    {
        $this->validate(
            'billCode'
        );
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->sandboxEndpoint : $this->productionEndpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new CompleteCreateBillResponse($this, $data);
    }

    public function getData()
    {
        $this->guardParameters();

        // $data = $this->httpRequest->request->all();
        $data = array(
            'billCode' => $this->getBillCode()
        );

        return $data;
    }

    public function sendData($data)
    {
        // $result = $this->httpClient->request('POST', $this->getEndpoint(), [
        //     'form_params' => $data
        // ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $this->getEndpoint() . $this->apiEndpoint);  
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($result, true);

        $data['redirectUrl'] = $this->getEndpoint() . $data['billCode'];
        $data['billpaymentStatus'] = $response[0]['billpaymentStatus'];
        $data['billpaymentInvoiceNo'] = $response[0]['billpaymentInvoiceNo'];
        $data['billName'] = $response[0]['billName'];


        return $this->createResponse($data);
    }

}