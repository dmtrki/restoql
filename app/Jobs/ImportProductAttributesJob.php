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
use App\Models\ProductAttribute;
use App\Models\Manufacturer;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\SystemEvent;
use Ramsey\Uuid\Uuid;

class ImportProductAttributesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
      $this->onQueue('import');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Product::select('id', 'uuid', 'category_id', 'details')
        ->whereNotNull('details')
        ->chunk(200, function($products) {
            foreach ($products as $product) {
                $this->addProductAttributes($product);
            }
        });
    }    

    public function addProductAttributes($product)
    {
      $options = $product->details['options'];
      foreach ($options as $propKey => $prop) {
        $formatedKey = Str::of($propKey)->replaceMatches('/[A-ZА-Я]/u', ' $0')->lower()->ltrim()->ucfirst();
        $valueRaw = explode(' ', $prop);
        $value = $valueRaw[0];
        $unit = $valueRaw[1] ?? null;

        // \Log::info('Из таблицы импорта получен аттрибут '.$formatedKey);
        
        $attribute = $this->addAttribute($formatedKey, $value, $unit);
        if (
          !ProductAttribute::where('product_id', $product->id)
          ->where('attribute_id', $attribute->id)
          ->first() !== null
        ) $product->attributes()
          ->attach($attribute, [
            'value' => $value, 
            'product_uuid' => $product->uuid, 
            'attribute_uuid' => $attribute->uuid
          ]);
        
        $this->addAttributeValueToProductCategory($product->category_id, $attribute->uuid, $value);
      }
    }

    public function addAttribute($formatedKey, $value, $unit)
    {
      $slug = Str::slug($formatedKey, '-');

      $attribute = Attribute::whereSlug($slug)->first();
      if ($attribute !== null) return $attribute;

      $attribute = Attribute::create([
        'title' => $formatedKey,
        'slug' => $slug,
        'unit' => $unit
      ]);

      return $attribute->fresh();
    }
    
    public function addAttributeValueToProductCategory($categoryId, $attributeUuid, $value)
    {

      $category = ProductCategory::select('uuid', 'title')->where('id', $categoryId)->first();
      if ($category === null) return;
  
      $categoryAttribute = DB::table('product_category_attributes')
                          ->select('values')
                          ->where('product_category_uuid', $category->uuid)
                          ->where('attribute_uuid', $attributeUuid)
                          ->first();
  
      $values = ($categoryAttribute !== null && $categoryAttribute->values !== null) ? json_decode($categoryAttribute->values) : [];
      if (is_array($values) && !in_array($value, $values)) $values[] = $value;
  
      DB::table('product_category_attributes')
      ->updateOrInsert([
        'product_category_uuid' => Uuid::fromString(strtolower($category->uuid))->getBytes(),
        'attribute_uuid' => Uuid::fromString(strtolower($attributeUuid))->getBytes(),
      ],[
        'values' => json_encode($values)
      ]);
    }
    
}
