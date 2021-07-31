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
    public function test()
    {
      $product = Product::whereNotNull('details')->inRandomOrder()->first();
      
    }
    public function importProducts()
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

        dd($product->attributes);
      }
    }

    public function addProduct($productData) 
    {
      $details = [
        'id' => $productData->id,
        'code' => $productData->code,
        'parent_id' => $productData->parent_id,
        'options' => $productData->options,
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

      return $product;
    }

    public function addProductImage($product)
    {
      $eId = $product->details['id'];
      $file = Storage::disk('import')->files('/image/'.$eId);
      if (!empty($file)) {
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/var/www/__imports/image/'.$file[0]);
        $supportedTypes = ['image/png', 'image/x-png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/webp'];
        if (!in_array($mime,$supportedTypes)) return;

        $product
          ->addMediaFromDisk($file[0], 'import')
          ->preservingOriginal()
          ->sanitizingFileName(function($fileName) {
            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
          })
          ->toMediaCollection('photos');
      }
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
        $product->attributes()->attach($attribute, ['value' => $value]);

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
        $xml = XML::import(storage_path('app/31-07-21.xml'))->get();
        $productsXml = $xml->products;
        foreach ($productsXml as $product) {
            if(!empty($product->currency) && !Currency::where('title',"$product->currency")->exists()){
                Currency::Create([
                    'title'    => "$product->currency",
                    'code' => '',
                    'symbol' => '',
                    'rate' => 1
                ]);
            }
        }
    }

    public function productImagesFiltered()
    {
      Product::doesntHave('media')->chunk(200, function($products) {
        foreach ($products as $product) {
          $eId = $product->details['id'];
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
      $categories = ProductCategory::all();

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
          $categories = ProductCategory::all();
        } else {
          $categories = ProductCategory::where('id', (int)$request->cat)->get();
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


    public function importCategories()
  {        
      $now = Carbon::now();
      $todayFileName = $now->format('d-m-y').'.xml';
      $pathToToday =storage_path('app/'.$todayFileName);
      $xml = XML::import($pathToToday)->get();
      $categoriesXml = $xml->categories;
      $categories = [];
      $onlyChildren = [];
      foreach ($categoriesXml as $category) {
          if (empty($category->parent_id)) {
            $details = [
              'id' => "$category->id",
              'code' => "$category->code",
              'parent_id' => null
            ];
            $categories["$category->id"] = [
              'title' => "$category->name",
              'details' => $details,
              'children' => []
            ];
          }
      }
      foreach ($categoriesXml as $category) {
        if (!empty($category->parent_id) && isset($categories["$category->parent_id"])) {
          $details = [
            'id' => "$category->id",
            'code' => "$category->code",
            'parent_id' => "$category->parent_id"
          ];
          $categories["$category->parent_id"]['children']["$category->id"] = [
            'title' => "$category->name",
            'details' => $details,
            'children' => []
          ];
          $onlyChildren["$category->id"] = $category;
        }
      }
      foreach ($onlyChildren as $category) {
        if (!empty($category->parent_id) && !isset($categories["$category->parent_id"])) {
          $details = [
            'id' => "$category->id",
            'code' => "$category->code",
            'parent_id' => "$category->parent_id"
          ];
          $parent = $onlyChildren["$category->parent_id"];
          $root = $categories["$parent->parent_id"];
          $root['children']["$category->parent_id"]['children'] = [
            'title' => "$category->name",
            'details' => $details,
            'children' => []
          ];
        }
      }
      $categoriesFormated = [];

      foreach ($categories as $key => $category) {
        $categoriesFormated[] = $category;
      }

      foreach ($categoriesFormated as $key => $item) {
        $children = [];
        foreach ($item['children'] as $childKey => $child) {
          $descenders = [];
          if (!empty($child['children'])) {
            foreach ($child['children'] as $downestKey => $downestChild) {
              $descenders[] = $downestChild;
            }                    
          }
          $child['children'] = $descenders;
          $children[] = $child;
        }
        $categoriesFormated[$key]['children'] = $children;
      }

      foreach ($categoriesFormated as $item) {
        if(!ProductCategory::where('title',$item['title'])->exists()){
          $creating = ProductCategory::create($item);
        }
      }

      $tree = ProductCategory::get()->toTree();

      dd($tree);
  }
}
