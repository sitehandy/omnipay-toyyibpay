<?php

namespace Omnipay\ToyyibPay\Message;

class CreateBillRequest extends AbstractRequest
{
    protected $productionEndpoint = 'https://toyyibpay.com/';

    protected $sandboxEndpoint = 'https://dev.toyyibpay.com/';

    protected $apiEndpoint = 'index.php/api/createBill';

    protected function guardParameters()
    {
        $this->validate(
            'userSecretKey',
            'categoryCode',
            'billName',
            'billDescription',
            'billPriceSetting',
            'billPayorInfo',
            'billAmount',
            'billReturnUrl',
            'billCallbackUrl',
            'billExternalReferenceNo',
            'billTo',
            'billEmail',
            'billPhone',
            'billSplitPayment',
            'billSplitPaymentArgs'
        );
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->sandboxEndpoint : $this->productionEndpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new CreateBillResponse($this, $data);
    }

    public function getData()
    {
        $this->guardParameters();

        // $data = $this->httpRequest->request->all();
        $data = array(
            'userSecretKey' => $this->getUserSecretKey(),
            'categoryCode' => $this->getCategoryCode(),
            'billName' => $this->getBillName(),
            'billDescription' => $this->getBillDescription(),
            'billPriceSetting' => $this->getBillPriceSetting(),
            'billPayorInfo' => $this->getBillPayorInfo(),
            'billAmount' => $this->getBillAmount(),
            'billReturnUrl' => $this->getBillReturnUrl(),
            'billCallbackUrl' => $this->getBillCallbackUrl(),
            'billExternalReferenceNo' => $this->getBillExternalReferenceNo(),
            'billTo' => $this->getBillTo(),
            'billEmail' => $this->getBillEmail(),
            'billPhone' => $this->getBillPhone(),
            'billSplitPayment' => $this->getBillSplitPayment(),
            'billSplitPaymentArgs' => $this->getBillSplitPaymentArgs(),
            'billPaymentChannel' => $this->getBillPaymentChannel(),
            'billDisplayMerchant' => $this->getBillDisplayMerchant(),
            'billContentEmail' => $this->getBillContentEmail()
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

        $data['BillCode'] = $response[0]['BillCode'];
        $data['redirectUrl'] = $this->getEndpoint() . $data['BillCode'];

        return $this->createResponse($data);
    }

}