<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Spatie\MediaLibrary\Models\Media;
use App\Models\ProductCategory;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;

class UpdateProductPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        // return now()->addMinutes(5);
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
            $product = Product::whereUuid($productData['id'])->first();
            
            if ($product) {
              $currency = (is_array($productData['currency'])) ? array_shift($productData['currency']) : $productData['currency'];
            
              $price = null;
              if ( $currency != 'руб.' && !empty($currency) && array_key_exists($currency, $rates) && $productData['cost']) {
                  $price = (float) $rates[$currency] * (float) $productData['cost'];
              } else {
                  $price = (float) $productData['cost'];
              }

              $product->price = $price;
              $product->save();

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
