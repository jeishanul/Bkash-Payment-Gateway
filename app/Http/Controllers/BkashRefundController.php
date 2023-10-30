<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BkashRefundController extends Controller
{
    private $base_url;
    private $app_key;

    public function __construct()
    {
        $this->app_key = env('BKASH_APP_KEY');
        $this->base_url = env('BKASH_BASE_URL');
    }

    public function refund(Request $request)
    {
        (new BkashPaymentController())->getToken();

        $token = session()->get('bkash_token');

        $this->validate($request, [
            'payment_id' => 'required',
            'amount' => 'required',
            'trx_id' => 'required',
            'sku' => 'required|max:255',
            'reason' => 'required|max:255'
        ]);

        $post_fields = [
            'paymentID' => $request->payment_id,
            'amount' => $request->amount,
            'trxID' => $request->trx_id,
            'sku' => $request->sku,
            'reason' => $request->reason,
        ];

        $refund_response = $this->refundCurl($token, $post_fields);

        if (array_key_exists('transactionStatus', $refund_response) && ($refund_response['transactionStatus'] === 'Completed')) {

            // IF REFUND PAYMENT SUCCESS THEN YOU CAN APPLY YOUR CONDITION HERE

            // THEN YOU CAN REDIRECT TO YOUR ROUTE

            return back()->with('successMsg', 'bKash Fund has been Refunded Successfully');
        }

        return back()->with('error', $refund_response['errorMessage']);
    }

    public function refundCurl($token, $post_fields)
    {
        $url = curl_init("$this->base_url/checkout/payment/refund");
        $header = array(
            'Content-Type:application/json',
            "authorization:$token",
            "x-app-key:$this->app_key"
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($post_fields));
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);

        return json_decode($resultdata, true);
    }
}
