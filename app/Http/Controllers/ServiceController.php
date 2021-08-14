<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Spatie\MediaLibrary\Models\Media;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\Attribute;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;

class ServiceController extends Controller
{
  protected $now;
  protected $todayFileName;
  protected $pathToToday;

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
    $this->now = Carbon::now();
    $this->todayFileName = $this->now->format('d-m-y').'.xml';
    $this->pathToToday =storage_path('app/'.$this->todayFileName);
  }


  public function test()
  {
    // dd(Product::doesntHave('category')->count());
    

    dump(ProductCategory::select('uuid')->get()->pluck('uuid'));
    
    $importNodes = DB::table('import_nodes')->inRandomOrder()->limit(108)->get();

    foreach ($importNodes as $key => $node) {
      $productData = json_decode($node->data);

      if (!Product::whereUuid((string) $productData->id)->exists()) {
        $this->addProduct($productData);
      } else {
        print('<h2>Товар уже существует</h2>');
        dump($productData);
      }
      
    }

    dd(Product::all());
    
  }


  public function importProducts()
  {
    $importNodes = DB::table('import_nodes')->get();

    foreach ($importNodes as $key => $node) {
      $productData = json_decode($node->data);

      if (!Product::whereUuid((string) $productData->id)->exists()) {
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
    
    $details['quantity'] = (array) $productData->quantity;

    $details['description'] =  (array) $productData->info;

    $creatingData = [
      'uuid' => $productData->id,
      // 'category_uuid' => $categoryUuid,
      // 'manufacturer_uuid' => $manufacturerUuid,
      'status_code' => 1,
      'title'    => $productData->name,
      'price' => 0,
      'rating' => 0,
      'views' => 0,
      'details' => $details,
    ];

    $product = Product::create($creatingData);
    $product = $product->fresh();

    $category = ProductCategory::whereUuid($productData->parent_id)->select('uuid')->first();
    if ($category === null) {      
      print('<h2>Категория не найдена нахуй</h2>');
      dump($details);
      dump($productData->parent_id);
      return null;
    }
    // $categoryUuid = $category->uuid;

    $product->category()->associate($category);

    $manufacturer = (
                      !empty($productData->proizvoditel) 
                      && is_string($productData->proizvoditel)
                      && !empty($productData->country)
                      && is_string($productData->country)
                    ) 
                    ? $this->addManufacturer($productData->proizvoditel, $productData->country)
                    : false;

    // $manufacturerUuid = ($manufacturer) ? $manufacturer->uuid : null;
    if ($manufacturer && $manufacturer !== null) {
      $product->manufacturer()->associate($manufacturer);
    } else {
      print('<h2>Производитель сука не найден ебать</h2>');
      dump($details);
      dump($productData->proizvoditel);
      return null;
    }

    $product->save();

    return $product;
  }


  public function addManufacturer($title, $country)
  {
    $manufacturer = Manufacturer::where('title', $title)->select('uuid')->first();

    if ($manufacturer !== null) return $manufacturer;

    $country = Country::where('title', $title)->select('code')->first();

    $manufacturer = Manufacturer::create([
      'title'    => $title,
      'country_code'  => $country->code
    ]);

    return $manufacturer->fresh();
  }

   
}
