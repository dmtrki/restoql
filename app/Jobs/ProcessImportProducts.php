<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use App\Models\ProductCategory;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;

class ProcessImportProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


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
      $this->importProducts();
    }

    public function importProducts()
    {
      $importNodes = DB::table('import_nodes')->get();

      foreach ($importNodes as $key => $node) {
        $productData = json_decode($node->data);

        if (!Product::where('title',(string) $productData->name)->exists()) {
          $this->addProduct($productData);
        }
        
      }
    }

    public function addProduct($productData) 
    {
      $details = [
        'external_id' => $productData->id,
        'code' => $productData->code,
        'parent_id' => $productData->parent_id,
        'options' => $productData->options,
        'price' => $productData->cost,
        'currency' => $productData->currency,
      ];
      
      $category = ProductCategory::where('details->id', "$productData->parent_id")->select('uuid')->first();

      if ($category === null) return null;
      $categoryUuid = $category->uuid;

      $manufacturer = Manufacturer::where('title', "$productData->proizvoditel")->select('uuid')->first();
      if ($manufacturer === null){
        $manufacturer = Manufacturer::create([
          'title'    => "$productData->proizvoditel",
          'country_code'  => "$productData->country"
        ]);

        $manufacturer = $manufacturer->fresh();
      }      

      $manufacturerUuid = $manufacturer ? $manufacturer->uuid : null;
      
      $details['quantity'] = (array) $productData->quantity;

      $details['description'] =  (array) $productData->info;


      $creatingData = [
        'uuid' => $productData->id,
        'category_uuid' => $categoryUuid,
        'manufacturer_uuid' => $manufacturerUuid,
        'status_code' => 1,
        'title'    => (string) $productData->name,
        'price' => 0,
        'rating' => 0,
        'views' => 0,
        'details' => $details,
      ];

      $product = Product::create($creatingData);
      // $product = $product->fresh();

      // return $product;
    }   
}
