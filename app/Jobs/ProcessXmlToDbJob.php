<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\SystemEvent;
use App\Models\Currency;
use Carbon\Carbon;
use XML;

class ProcessXmlToDbJob extends ImportBaseJob
{
    //public $tries = 60;

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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $this->addCurrencies();

      $processingEvent = SystemEvent::select('id')
                        ->where('type_code', 100)
                        ->where('status_code', 1)
                        ->whereDate('created_at', $this->now->format('y-m-d'))
                        ->first();

      if ($processingEvent !== null) return;

      $xml = $this->getXmlFileContent();
      $productsXml = $xml->products;

      foreach ($productsXml->item as $productXml) {
        $options = [
          'Гарантия' => (!empty($productXml->garanty)) ? "$productXml->garanty" : null,
          'Срок поставки' => (!empty($productXml->srok_postavki)) ? "$productXml->srok_postavki дней" : null,
          'Длина' => (!empty($productXml->lenght)) ? "$productXml->lenght мм" : null,
          'Ширина' => (!empty($productXml->width)) ? "$productXml->width мм" : null,
          'Высота' => (!empty($productXml->height)) ? "$productXml->height мм" : null,
        ];

        $optionsXml = $productXml->options;

        $iterator = 0;
        foreach ($productXml->options->children() as $key => $option) {
          $unit = ''.$optionsXml->options_item[$iterator];
          $attributes = current($option->attributes());

          foreach ($attributes as $attributesKey => $value) {
              if ($value == '0') continue;
              $options["$attributesKey"] = $value.' '.$unit;
          }
          $iterator++;
        }

        $data = json_decode( json_encode($productXml) );
        $data->options = $options;
        DB::table('import_nodes')
            ->updateOrInsert(
                ['uuid' => $data->id],
                ['data' => json_encode($data)]
            );
      }
    }

    public function addCurrencies()
    {
      $xml = $this->getXmlFileContent();
      $productsXml = $xml->products;

        foreach ($productsXml as $product) {
            if(!empty($product->currency) && !Currency::where('title',"$product->currency")->exists()){
                Currency::create([
                    'title'    => "$product->currency",
                    'code' => '',
                    'symbol' => '',
                    'rate' => 1
                ]);
            }
        }
    }
}
