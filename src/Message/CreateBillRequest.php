<?php

namespace Omnipay\ToyyibPay\Message;

class CreateBillRequest extends AbstractRequest
{
    protected $productionEndpoint = 'https://' . $this->baseEndpoint . 'index.php/api/createBill';

    protected $sandboxEndpoint = 'https://dev.' . $this->baseEndpoint . 'index.php/api/createBill';

    public function getData()
    {
        $data = array(
            'userSecretKey' => $this->getUserSecretKey(),
            'categoryCode' => $this->getCategoryCode(),
            'billName' => $this->getBillName(),
            'billDescription' => $this->getBillDescription(),
            'billPriceSetting'=> $this->getBillPriceSetting(),
            'billPayorInfo'=> $this->getBillPayorInfo(),
            'billAmount'=> $this->getBillAmount(),
            'billReturnUrl'=> $this->getBillReturnUrl(),
            'billCallbackUrl'=> $this->getBillCallbackUrl(),
            'billExternalReferenceNo' => $this->getBillExternalReferenceNo(),
            'billTo'=> $this->getBillTo(),
            'billEmail'=> $this->getBillEmail(),
            'billPhone'=> $this->getBillPhone(),
            'billSplitPayment'=> $this->getBillSplitPayment(),
            'billSplitPaymentArgs'=> $this->getBillSplitPaymentArgs(),
            'billPaymentChannel'=> $this->getBillPaymentChannel(),
            'billDisplayMerchant'=> $this->getBillDisplayMerchant(),
            'billContentEmail'=> $this->getBillContentEmail()
          );
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
        return $this->response = new CreateBillResponse($this, $data);
    }

}