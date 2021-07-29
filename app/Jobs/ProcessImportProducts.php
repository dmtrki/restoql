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
use Spatie\MediaLibrary\Models\Media;
use App\Models\Category;
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

    private $now;
    private $todatFilename;
    private $pathToToday;

    //public $tries = 60;
    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(45);
    }


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->onQueue('import');
      $this->now = Carbon::now();
      $this->todayFileName = $this->now->format('d-m-y').'.xml';
      $this->pathToToday =storage_path('app/'.$this->todayFileName);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $importNodes = DB::table('import_nodes')->get();

      foreach ($importNodes as $key => $node) {
        $productData = json_decode($node->data);

        if (!Product::where('title',(string) $productData->name)->exists()) {
          $product = $this->addProduct($productData);
        } else {
          $product = Product::where('title',(string) $productData->name)
                              ->whereDate('updated_at', '<', $this->now->format('Y-m-d'))
                              ->first();
        }
        
        DB::table('import_nodes')->where('id', $node->id)->delete();
        if ($product === null) continue;
        $isModifed = true;

        //$this->addProductImage($product);
        $this->addProductAttributes($product);
        $product = $this->updateProductPrices($product, $productData);
        $product->save();
      }
    }

    public function addProduct($productData) 
    {
      $external = [
        'id' => $productData->id,
        'code' => $productData->code,
        'parent_id' => $productData->parent_id,
        'options' => $productData->options
      ];
      
      $category = Category::where('external->id', "$productData->parent_id")->select('id')->first();

      if ($category === null) return null;
      $categoryId = $category->id;

      $manufacturer = Manufacturer::where('title', "$productData->proizvoditel")->select('id')->first();
      if ($manufacturer === null){
        $manufacturer = Manufacturer::Create([
          'title'    => "$productData->proizvoditel",
          'country'  => "$productData->country"
        ]);
      }
      $manufacturer = $manufacturer->fresh();
      $manufacturerId = $manufacturer->id;

      $info = (array) $productData->info;
      if (empty($info)) {
        $info = 0;
      } elseif (isset($info[0])) {
        $info = $info[0];
      }
      $quantity = (array) $productData->quantity;
      if (empty($quantity)) {
        $quantity = 0;
      } elseif (isset($quantity[0])) {
        $quantity = $quantity[0];
      }

      $product = Product::Create([
        'title'    => (string) $productData->name,
        'description'  => $info,
        'external' => $external,
        'category_id' => $categoryId,
        'manufacturer_id' => $manufacturerId,
        'quantity' => $quantity,
      ]);

      return $product;
    }

    public function addProductImage($product)
    {
      $eId = $product->external['id'];
      $file = Storage::disk('import')->files('/image/'.$eId);
      if (!empty($file)) {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/var/www/www-root/data/importing/'.$file[0]);
        $supportedTypes = ['image/png', 'image/x-png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/webp'];
        if (!in_array($mime,$supportedTypes)) return;

        $product
          ->addMediaFromDisk($file[0], 'import')
          ->preservingOriginal()
          ->sanitizingFileName(function($fileName) {
            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
          })
          ->toMediaCollection('main');
      }
    }

    public function addProductAttributes($product)
    {
      $options = $product->external['options'];
      foreach ($options as $propKey => $prop) {
        $formatedKey = Str::of($propKey)->replaceMatches('/[A-ZА-Я]/u', ' $0')->lower()->ltrim()->ucfirst();
        $value = explode(' ', $prop);
        $unit = $value[1] ?? null;
        $value = $value[0] ?? null;
        $slug = Str::slug($formatedKey, '-');
        
        if(!Attribute::where('slug',$slug)->exists()){
          $attribute = Attribute::create([
            'title' => $formatedKey,
            'slug' => $slug,
            'unit' => $unit
          ]);

          DB::table('category_attributes')->insertOrIgnore([
              'category_id' => $product->category_id,
              'attribute_id' => $attribute->id
          ]);

          DB::table('product_attribute_values')->insertOrIgnore([
              'product_id' => $product->id,
              'attribute_id' => $attribute->id,
              'value' => $value
          ]);
        } else {
          $attribute = Attribute::where('slug',$slug)->first();
          DB::table('product_attribute_values')->updateOrInsert(
            [
              'product_id' => $product->id,
              'attribute_id' => $attribute->id,
            ],
            [
              'product_id' => $product->id,
              'attribute_id' => $attribute->id,
              'value' => $value
            ]
          );
        }
      }
    }

    public function updateProductPrices($product, $productData)
    {
      $rates = $this->getRates();
      $currency = $productData->currency;

      if ( $currency != 'руб.' && $currency != '' && $rates[$currency] && $productData->cost) {
        $price = $rates[$currency] * (double) $productData->cost;
      } else {
        $price = (double) $productData->cost;
      }

      $product->price = $price;

      return $product;
    }


    

    public function addCurrencies()
    {
        $xml = XML::import(storage_path('08-02-21.xml'))->get();
        $productsXml = $xml->products;
        foreach ($productsXml as $product) {
            if(!empty($product->currency) && !Currency::where('title',"$product->currency")->exists()){
                Currency::Create([
                    'title'    => "$product->currency",
                    'code' => '',
                    'symbol' => '',
                ]);
            }
        }
    }

    public function productImagesFiltered()
    {
      Product::doesntHave('media')->chunk(200, function($products) {
        foreach ($products as $product) {
          $eId = $product->external['id'];
          $file = Storage::disk('import')->files('/image/'.$eId);
          if (!empty($file)) {
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/var/www/www-root/data/importing/'.$file[0]);
            $supportedTypes = ['image/png', 'image/x-png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/webp'];
            if (!in_array($mime,$supportedTypes)) continue;

            $product
              ->addMediaFromDisk($file[0], 'import')
              ->preservingOriginal()
              ->sanitizingFileName(function($fileName) {
                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
              })
              ->toMediaCollection('main');
          }
        }
      });
    }

    public function categoriesMinMax(Request $request)
    {
      $categories = Category::all();

      foreach ($categories as $key => $category) {
       

        $maxPrice = DB::table('products')
                      ->where('category_id', $category->id)                      
                      ->where('price', '!=', 0)
                      ->max('price');
        $minPrice = DB::table('products')
          ->where('category_id', $category->id)
          ->where('price', '!=', 0)
          ->min('price');
        
        
        Redis::set('category_price_range:'.$category->id, $minPrice.'-'.$maxPrice);
        var_dump(Redis::get('category_price_range:'.$category->id));
        echo '<hr/>';
      }
      
    }

    public function generateThumbs(Request $request)
    {
      echo '<hr/>';
      echo '<hr/>';
      if ($request->cat) {
        if ($request->cat == 'all') {
          $categories = Category::all();
        } else {
          $categories = Category::where('id', (int)$request->cat)->get();
        }

        foreach ($categories as $key => $category) {
          $category->regenerateThumbUrls();
          echo '<b>'.$category->title . '</b><br/>'.Redis::get('category_thumb_url:'.$category->id).'<br><img src="'.Redis::get('category_thumb_url:'.$category->id).'" /><hr>';
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
      }
      return $rates;
    }
}
