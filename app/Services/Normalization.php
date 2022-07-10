<?php
namespace App\Services;

use App\Payment;
use App\PosSetting;
use App\Product_Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Normalization
{
    public  $data;
    public  $sale;
    public  $token;

    public function __construct($sale) {
        $this->sale = $sale;
    }

    public function normalize()
    {
        $this->token = PosSetting::where('normalization_token', '!=', null)->first()->normalization_token ?? "";
        try {
            $response = Http::withToken($this->token)->post(env("NORM_BASE_URL").'/api/invoice', $this->mapData());

            if($response->successful()){
                $uid = $response->object()->uid;
                $this->confirm($uid);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $message = 'Something went wrong when trying to access to API';
        }
    }

    public function confirm($uid)
    {
        try {
            $response = Http::withToken($this->token )->put(env("NORM_BASE_URL").'/api/invoice/'.$uid.'/confirm', $this->mapData());
            // dd($response->object());
            if($response->successful()){
                $this->updateSale($response->object());
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $message = 'Something went wrong when trying to access to API';
        }
    }

    public function mapData()
    {
            return [
                "ifu" => $this->getIfu(),
                "aib" => "A",
                "type" => "FV",
                "items" => $this->getItems(),
                "client" => $this->getClient(),
                "operator" => $this->getOperator(),
                "payment" => $this->getPayement(),
                "reference" => $this->getReference(),
            ];
    }

    public function getItems()
    {
        return Product_Sale::join('products', 'products.id', 'product_sales.product_id')->where('sale_id', $this->sale->id)->get()

        ->map(function($item){
            return [
                "code" => $item->code,
                "name" => $item->name,
                "price" => $item->price,
                "quantity" => $item->qty,
                #  need more explanation
                "taxGroup" => "e",
                "taxSpecific" => $item->tax_rate,
                "originalPrice" => $item->net_unit_price,
                "priceModification" => ""
            ];
        });
    }

    public function getOperator()
    {
        $operator = $this->sale->user;
        return [
            "id" =>$operator->name,
            "name" => $operator->name,
        ];
    }

    public function getPayement()
    {
        $payments = Payment::where('sale_id', $this->sale->id)->get();

        return $payments->map(function($payment){
            return [
                "name" => /* $payment->paying_method */"ESPECES",
                "amount" => $payment->amount,
            ];
        });
    }

    public function getClient()
    {
        $client = $this->sale->customer;
        return [
            "ifu" => "",
            "name" => $client->name,
            "contact" => $client->phone_number,
            "address" => $client->address,
        ];
    }

    public function getIfu()
    {
        $ifu = PosSetting::get()->last()->ifu ?? "";
        return $ifu;
    }

    public function getReference()
    {
        return $this->sale->reference_no;
    }

    /*
    l'information taxgroup est elle static?
    gerer les moyens de payements

    */


    public function updateSale($response)
    {
        $this->sale->normalization_status = true;
        $this->sale->normalization_code = $response->codeMECeFDGI;
        $this->sale->nim_code = $response->nim;
        $this->sale->normalization_date = $response->dateTime;
        $this->sale->qr_code = $response->qrCode;
        $this->sale->normalization_counters = $response->counters;

        $this->sale->save();
    }
}
