<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductDiscount;
use Carbon\Carbon;
use XML;

class UpdateProductPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->onQueue('import');
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $importNodes = DB::table('import_nodes')->get();
        $rates = $this->getRates();

        foreach ($importNodes as $key => $node) {

            $productData = json_decode($node->data, true);
            $product = Product::select('id', 'uuid', 'price')->whereUuid($productData['id'])->first();
            
            if ($product) {
              $currency = (is_array($productData['currency'])) ? array_shift($productData['currency']) : $productData['currency'];
              if ($currency == '') $currency = 'RUB';
              $currency = Currency::where('title', $currency)->orWhere('code', $currency)->first();
            
              $price = null;
              $cost = number_format((float) $productData['cost'], 2, '.', '');

              if ($currency->is_default !== 0) {
                $price = $cost;
              } else {
                $price = (float) $rates[$currency->code] * $cost;
              }
// TODO: disount exceptions

              $price = number_format($price, 2, '.', '');
              $product->price = $price;
              $product->save();

              $productPrice = ProductPrice::where('product_id', $product->id)->first();

              if ($productPrice === null) {
                ProductPrice::create([
                  'product_id' => $product->id,
                  'product_uuid' => $product->uuid,
                  'currency_id' => $currency->id,
                  'price_origin' => $cost,
                  'price' => $price,
                ]);
              } else {
                $productPrice->currency_id = $currency->id;
                $productPrice->price_origin = $cost;
                $productPrice->price = $price;
                $productPrice->save();
              }

              DB::table('import_nodes')->where('id', $node->id)->delete();

            }
        }
        

    }

    public function getRates()
    {
      $cbRates = XML::import('http://www.cbr.ru/scripts/XML_daily.asp')->get();
      $cbRates = $cbRates['Valute'];
      $rates = [];
      foreach ($cbRates as $key => $rate) {
        if ($rate->CharCode == 'EUR') {
          $rates['EUR'] = number_format(floatval(str_replace(",", ".", $rate->Value)), 2, '.', '');
        }
        if ($rate->CharCode == 'USD') {
          $rates['USD'] = number_format(floatval(str_replace(",", ".", $rate->Value)), 2, '.', '');
        }
        if ($rate->CharCode == 'GBP') {
          $rates['GBP'] = number_format(floatval(str_replace(",", ".", $rate->Value)), 2, '.', '');
        }
      }
      return $rates;
    }
}
