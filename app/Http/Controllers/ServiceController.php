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
use App\Models\ProductAttribute;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\Attribute;
use App\Models\Country;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;
use Ramsey\Uuid\Uuid;

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
    Product::select('id', 'uuid', 'category_id', 'details')
      ->whereNotNull('details')
      ->chunk(200, function($products) {
        foreach ($products as $product) {
          dump('Добавление аттрибутов для '.$product->id);
          $this->addProductAttributes($product);
        }
        exit;
      });

    exit;
    // dd(Product::doesntHave('category')->count());    

    dump(ProductCategory::select('uuid')->get()->pluck('uuid'));
    
    // $importNodes = DB::table('import_nodes')->inRandomOrder()->limit(108)->get();
    $importNodes = DB::table('import_nodes')->get();

    foreach ($importNodes as $key => $node) {
      $productData = json_decode($node->data);

      if (!Product::whereUuid((string) $productData->id)->exists()) {
        $this->addProduct($productData);
      }
      
    }
    
  }

  public function importNodes()
  {
     $xml = XML::import($this->pathToToday)->get();
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
  
        $itterator = 0;
        foreach ($productXml->options->children() as $key => $option) {
          $unit = ''.$optionsXml->options_item[$itterator];
          $attributes = current($option->attributes());
  
          foreach ($attributes as $attributesKey => $value) {
              if ($value == '0') continue;
              $options["$attributesKey"] = $value.' '.$unit;
          }
          $itterator++;
        }

        $data = json_decode( json_encode($productXml) );

        $exist = DB::table('import_nodes')->select('uuid')->where('uuid', $data->id)->first();
        if ($exist !== null) {
          // dump($exist);
          continue;
        }

        $data->options = $options;

        dump($data->id);
        DB::table('import_nodes')->insert([
          'uuid' => $data->id,
          'data' => json_encode($data)
        ]);
      }

      exit;
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

    $category = ProductCategory::whereUuid($productData->parent_id)->select('id', 'uuid')->first();
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

  public function addCountry($title)
  {
    $country = Country::where('title', $title)->select('code')->first();

    if ($country !== null) return $country;

    $code = array_search($title, $this->countryCodes);
    $country = Country::create([
      'title' => $title,
      'code' => $code ?? null,
    ]);
    
    return $country->fresh();
  }


  public function addManufacturer($title, $country)
  {
    $manufacturer = Manufacturer::where('title', $title)->select('uuid')->first();

    if ($manufacturer !== null) return $manufacturer;

    $country = $this->addCountry($country);

    $manufacturer = Manufacturer::create([
      'title'    => $title,
      'country_code'  => $country->code
    ]);

    return $manufacturer->fresh();
  }

  public function attrs()
  {
      Product::select('id', 'uuid', 'category_uuid', 'details')
      ->whereNotNull('details')
      ->chunk(200, function($products) {
          foreach ($products as $product) {
              // \Log::info('Добавление аттрибутов для '.$product->id);
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

      dump('Из таблицы импорта получен аттрибут '.$formatedKey);
      
      $attribute = $this->addAttribute($formatedKey, $value, $unit);
      dump('Он же '.$attribute);
      dump('Со значением: '.$value);
      if (ProductAttribute::where('product_id', $product->id)->where('attribute_id', $attribute->id)->first() !== null) {
        dump('Бля, такой аттриббутт уже есть');
      } else {
        $product->attributes()->attach($attribute, ['value' => $value, 'product_uuid' => $product->uuid, 'attribute_uuid' => $attribute->uuid]);
      }
      dump('Теперь товар имеет следующие аттрибуты:');
      dump($product->attributes);
      dump($product);
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
    dump('А бля категории '.$categoryId);
    $category = ProductCategory::select('uuid', 'title')->where('id', $categoryId)->first();
    if ($category === null) return;

    dump('А для категории '.$category->title.' добавляем значения');
    $categoryAttribute = DB::table('product_category_attributes')
                        ->select('values')
                        ->where('product_category_uuid', $category->uuid)
                        ->where('attribute_uuid', $attributeUuid)
                        ->first();

    $values = ($categoryAttribute !== null && $categoryAttribute->values !== null) ? json_decode($categoryAttribute->values) : [];
    dump($values);
    if (is_array($values) && !in_array($value, $values)) $values[] = $value;
    dump($values);

    DB::table('product_category_attributes')
    ->updateOrInsert([
      'product_category_uuid' => Uuid::fromString(strtolower($category->uuid))->getBytes(),
      'attribute_uuid' => Uuid::fromString(strtolower($attributeUuid))->getBytes(),
    ],[
      'values' => json_encode($values)
    ]);
  }


  private $countryCodes = array (
    'AC' =>'остров Вознесения', 
    'AD' => 'Андорра', 
    'AE' => 'Объединенные Арабские Эмираты', 
    'AF' => 'Афганистан', 
    'AG' => 'Антигуа и Барбуда', 
    'AI' => '', 
    'AL' => 'Албания', 
    'AM' => 'Армения', 
    'AN' => 'Голланские Антильские острова', 
    'AO' => 'Ангола', 
    'AQ' => 'Антарктика', 
    'AR' => 'Аргентина', 
    'AS' => 'Американское Самоа', 
    'AT' => 'Австрия', 
    'AU' => 'Австралия', 
    'AW' => 'Аруба', 
    'AX' => 'Аландские острова', 
    'AZ' => 'Азербайджан', 
    'BA' => 'Босния и Герцеговина', 
    'BB' => 'Барбадос', 
    'BD' => 'Бангладеш', 
    'BE' => 'Бельгия', 
    'BF' => 'Буркина-Фасо', 
    'BG' => 'Болгария', 
    'BH' => 'Бахрейн', 
    'BI' => 'Бурунди', 
    'BJ' => 'Бенин', 
    'BM' => 'Бермудские острова', 
    'BN' => 'Бруней', 
    'BO' => 'Боливия', 
    'BR' => 'Бразилия', 
    'BS' => 'Багамские острова', 
    'BT' => 'Бутан', 
    'BV' => '', 
    'BW' => 'Ботсвана', 
    'BY' => 'Беларусь', 
    'BZ' => 'Белиз', 
    'CA' => 'Канада', 
    'CC' => 'Кокосовые острова', 
    'CD' => 'Конго', 
    'CF' => 'Центральноафриканская Республика', 
    'CG' => 'Конго', 
    'CH' => 'Швейцария', 
    'CI' => 'Кот-дИвуар', 
    'CK' => 'острова Кука', 
    'CL' => 'Чили', 
    'CM' => 'Камерун', 
    'CN' => 'Китай', 
    'CO' => 'Колумбия', 
    'CR' => 'Коста-Рика', 
    'CS' => 'Сербия и Черногория', 
    'CU' => 'Куба', 
    'CV' => 'Кабо-Верде', 
    'CX' => 'остров Рождества', 
    'CY' => 'Кипр', 
    'CZ' => 'Чехия', 
    'DE' => 'Германия', 
    'DJ' => 'Джибути', 
    'DK' => 'Дания', 
    'DM' => 'Доминика', 
    'DO' => 'Доминиканская Республика', 
    'DZ' => 'Алжир', 
    'EC' => 'Эквадор', 
    'EE' => 'Эстония', 
    'EG' => 'Египет', 
    'EH' => 'Западная Сахара', 
    'ER' => 'Эритрея', 
    'ES' => 'Испания', 
    'ET' => 'Эфиопия', 
    'FI' => 'Финляндия', 
    'FJ' => 'Фиджи', 
    'FK' => 'Фолклендские острова', 
    'FM' => 'Микронезия', 
    'FO' => 'Фарерские острова', 
    'FR' => 'Франция', 
    'GA' => 'Габон', 
    'GB' => 'Соединенное Королевство Великобритании и Северной Ирландии', 
    'GD' => 'Гренада', 
    'GE' => 'Грузия', 
    'GF' => 'Французская Гвиана', 
    'GG' => 'остров Гернси', 
    'GH' => 'Гана', 
    'GI' => 'Гибралтар', 
    'GL' => 'Гренландия', 
    'GM' => 'Гамбия', 
    'GN' => 'Гвинея', 
    'GP' => 'Гваделупа', 
    'GQ' => 'Экваториальная Гвинея', 
    'GR' => 'Греция', 
    'GS' => 'Южная Джорджия и Южные Сандвичевы острова', 
    'GT' => 'Гватемала', 
    'GU' => 'Гуам', 
    'GW' => 'Гвинея-Бисау', 
    'GY' => 'Гайана', 
    'HK' => 'Гонконг', 
    'HM' => '', 
    'HN' => 'Гондурас', 
    'HR' => 'Хорватия', 
    'HT' => 'Гаити', 
    'HU' => 'Венгрия', 
    'ID' => 'Индонезия', 
    'IE' => 'Ирландия', 
    'IL' => 'Израиль', 
    'IM' => 'остров Мэн', 
    'IN' => 'Индия', 
    'IO' => '', 
    'IQ' => 'Ирак', 
    'IR' => 'Иран', 
    'IS' => 'Исландия', 
    'IT' => 'Италия', 
    'JE' => 'остров Джерси', 
    'JM' => 'Ямайка', 
    'JO' => 'Иордания', 
    'JP' => 'Япония', 
    'KE' => 'Кения', 
    'KG' => 'Кыргызстан', 
    'KH' => 'Камбоджа', 
    'KI' => 'Кирибати', 
    'KM' => 'Коморские острова', 
    'KN' => 'Сент-Китс и Невис', 
    'KP' => 'Северная Корея', 
    'KR' => 'Южная Корея', 
    'KW' => 'Кувейт', 
    'KY' => 'Каймановы острова', 
    'KZ' => 'Казахстан', 
    'LA' => 'Лаос', 
    'LB' => 'Ливан', 
    'LC' => 'Сент-Люсия', 
    'LI' => 'Лихтенштейн', 
    'LK' => 'Шри-Ланка', 
    'LR' => 'Либерия', 
    'LS' => 'Лесото', 
    'LT' => 'Литва', 
    'LU' => 'Люксембург', 
    'LV' => 'Латвия', 
    'LY' => 'Ливия', 
    'MA' => 'Марокко', 
    'MC' => 'Монако', 
    'ME' => 'Монтенегро',
    'MD' => 'Молдова', 
    'MG' => 'Мадагаскар', 
    'MH' => 'Маршалловы острова', 
    'MK' => 'Македония', 
    'ML' => 'Мали', 
    'MM' => 'Мьянма', 
    'MN' => 'Монголия', 
    'MO' => 'Макао', 
    'MP' => 'Mariana  Северные Марианские острова', 
    'MQ' => 'Мартиника', 
    'MR' => 'Мавритания', 
    'MS' => 'Монтсеррат', 
    'MT' => 'Мальта', 
    'MU' => 'Маврикий', 
    'MV' => 'Мальдивы', 
    'MW' => 'Малави', 
    'MX' => 'Мексика', 
    'MY' => 'Малайзия', 
    'MZ' => 'Мозамбик', 
    'NA' => 'Намибия', 
    'NC' => 'Новая Каледония', 
    'NE' => 'Нигер', 
    'NF' => 'Норфолк', 
    'NG' => 'Нигерия', 
    'NI' => 'Никарагуа', 
    'NL' => 'Нидерланды', 
    'NO' => 'Норвегия', 
    'NP' => 'Непал', 
    'NR' => 'Науру', 
    'NU' => '', 
    'NZ' => 'Новая Зеландия', 
    'OM' => 'Оман', 
    'PA' => 'Панама', 
    'PE' => 'Перу', 
    'PF' => 'Французская Полинезия', 
    'PG' => 'Папуа - Новая Гвинея', 
    'PH' => 'Филиппины', 
    'PK' => 'Пакистан', 
    'PL' => 'Польша', 
    'PM' => 'Сен-Пьер и Микелон', 
    'PN' => 'остров Питкэрн', 
    'PR' => 'Пуэрто-Рико', 
    'PS' => 'Палестина', 
    'PT' => 'Португалия', 
    'PW' => 'Палау', 
    'PY' => 'Парагвай', 
    'QA' => 'Катар', 
    'RE' => 'остров Реюньон', 
    'RO' => 'Румыния', 
    'RU' => 'Россия', 
    'RW' => 'Руанда', 
    'SA' => 'Саудовская Аравия', 
    'SB' => 'Соломоновы Острова', 
    'SC' => 'Сейшельские Острова', 
    'SD' => 'Судан', 
    'SE' => 'Швеция', 
    'SG' => 'Сингапур', 
    'SH' => 'остров Святой Елены', 
    'SI' => 'Словения', 
    'SJ' => '', 
    'SK' => 'Словакия', 
    'SL' => 'Сьерра-Леоне', 
    'SM' => 'Сан-Марино', 
    'SN' => 'Сенегал', 
    'SO' => 'Сомали', 
    'SR' => 'Суринам', 
    'ST' => 'Сан-Томе и Принсипи', 
    'SU' => 'СССР', 
    'SV' => 'Сальвадор', 
    'SY' => 'Сирия', 
    'SZ' => 'Свазиленд', 
    'TC' => '', 
    'TD' => 'Чад', 
    'TF' => '', 
    'TG' => 'Того', 
    'TH' => 'Таиланд', 
    'TJ' => 'Таджикистан', 
    'TK' => 'Токелау', 
    'TL' => '-', 
    'TM' => 'Туркменистан', 
    'TN' => 'Тунис', 
    'TO' => 'Тонга', 
    'TP' => 'Восточный Тимор', 
    'TR' => 'Турция', 
    'TT' => 'Тринидад и Тобаго', 
    'TV' => 'Тувалу', 
    'TW' => 'Тайвань', 
    'TZ' => 'Танзания', 
    'UA' => 'Украина', 
    'UG' => 'Уганда', 
    'UK' => 'Соединенное Королевство Великобритании и Северной Ирландии', 
    'UM' => '', 
    'US' => 'США', 
    'UY' => 'Уругвай', 
    'UZ' => 'Узбекистан', 
    'VA' => 'Ватикан', 
    'VC' => 'Сент-Винсент и Гренадины', 
    'VE' => 'Венесуэла', 
    'VG' => 'Виргинские острова, Британские', 
    'VI' => 'Виргинские острова, США', 
    'VN' => 'Вьетнам', 
    'VU' => 'Вануату', 
    'WF' => '', 
    'WS' => 'Западное Самоа', 
    'YE' => 'Йемен', 
    'YT' => '', 
    'YU' => 'Югославия', 
    'ZA' => 'ЮАР', 
    'ZM' => 'Замбия', 
    'ZW' => 'Зимбабве'
  );
   
}
