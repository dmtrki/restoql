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
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;

class ImportProductPhotosAandAttributes implements ShouldQueue
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
        return now()->addMinutes(10);
    }
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->productImagesImport();
        $this->productAttributesImport();
    }

    public function productImagesImport()
    {
      Product::doesntHave('media')->select('id','uuid')->chunk(200, function($products) {
        foreach ($products as $product) {
          $file = Storage::disk('import')->files('/image/'.$product->uuid);
          if (!empty($file)) {
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/var/www/__imports/'.$file[0]);
            $supportedTypes = ['image/png', 'image/x-png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/webp'];
            if (!in_array($mime,$supportedTypes)) continue;

            $product
              ->addMediaFromDisk($file[0], 'import')
              ->preservingOriginal()
              ->sanitizingFileName(function($fileName) {
                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
              })
              ->toMediaCollection('photos');

          }
        }
      });
    }

    public function productAttributesImport()
    {
      Product::whereNotNull('details')->chunk(200, function($products) {
        foreach ($products as $product) {
          $product->addProductAttributes();
        }
      });
    }

    public function addProductAttributes($product)
    {
      $options = $product->details['options'];
      foreach ($options as $propKey => $prop) {
        $formatedKey = Str::of($propKey)->replaceMatches('/[A-ZА-Я]/u', ' $0')->lower()->ltrim()->ucfirst();
        $value = explode(' ', $prop);
        $unit = $value[1] ?? null;
        $value = $value[0] ?? null;
        $slug = Str::slug($formatedKey, '-');
        
        $attribute = Attribute::whereSlug($slug)->first();
        if($attribute === null){
          $attribute = Attribute::create([
            'title' => $formatedKey,
            'slug' => $slug,
            'unit' => $unit
          ]);
        }
        $product->attributes()->attach($attribute, ['value' => $value, 'product_uuid' => $product->uuid, 'attribute_uuid' => $attribute->uuid]);
      }
    }
}
