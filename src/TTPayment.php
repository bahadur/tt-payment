<?php

namespace TTPayment\LaravelTTPayment;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use TTPayment\LaravelTTPayment\Models\Payment;

class TTPayment
{
    protected $client;
    protected $testEndpoint = "https://igw-seb-demo.every-pay.com/api/v4";
    protected $liveEndpoint = "https://payment.ecommerce.sebgroup.com/api/v4";
    protected $apiEndpointUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        if(config('laravel-tt-payment.sandbox')) {
            $this->apiEndpointUrl = $this->testEndpoint;
        } else {
            $this->apiEndpointUrl = $this->liveEndpoint;
        }


        $this->username = config('laravel-tt-payment.username');
        $this->password = config('laravel-tt-payment.password');

        //$this->client = new Client();
    }

    public function makePayment($paymentDetails, $paymentType = "oneoff")
    {

        $endpoint = "/payments/$paymentDetails";
        $param = [
            "timestamp" => Carbon::now()->format('c'),
            "request_token" => true,
            "token_agreement" => "unscheduled",
            "customer_ip" => $_SERVER['REMOTE_ADDR'],
            "api_username" => $this->username,
            "nonce" => $this->nonce(),

        ];

        $params = array_merge($paymentDetails, $param);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("$this->username:$this->password"),
                'Content-Type' => 'application/json'
            ])->post($this->apiEndpointUrl . $endpoint, $params);

            $payment = new Payment();

            $payment->payment_reference = $response['payment_reference'];
            $payment->initiated_at = now();
            $payment->save();
            return $response['payment_link'];
        } catch(\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function paymentStatus($paymentReference)
    {
        try {
            return Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("$this->username:$this->password"),
                'Content-Type' => 'application/json'
            ])->get($this->apiEndpointUrl . '/payments/' . $paymentReference);

        } catch(\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function paymentRefund($paymentReference, $amount)
    {
        $param = [
            "timestamp" => Carbon::now()->format('c'),
            "api_username" => $this->username,
            "nonce" => $this->nonce(),
            "payment_reference" => $paymentReference,
            "amount" => $amount

        ];

        try {
            return Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("$this->username:$this->password"),
                'Content-Type' => 'application/json'
            ])->post($this->apiEndpointUrl . '/payments/refund', $param);

        } catch(\Exception $e) {
            dd($e->getMessage());
        }
    }

    protected function nonce()
    {
        $random = '';
        for ($i = 0; $i < 32; $i++) {
            $random .= chr(mt_rand(0, 255));
        }
        return hash('sha512', $random);
    }

}
