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
use App\Models\Country;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\SystemEvent;
use Carbon\Carbon;
use XML;

class ImportProductsJob implements ShouldQueue
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
        logger('Категория не найдена нахуй', $details);
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
                      : null;

      // $manufacturerUuid = ($manufacturer) ? $manufacturer->uuid : null;
      if ($manufacturer !== null) {
        logger('Производитель сука не найден ебать', $details);
        return null;
      }
      $product->manufacturer()->associate($manufacturer);

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
      $manufacturer = Manufacturer::where('title', $title)->select('id', 'uuid')->first();

      if ($manufacturer !== null) return $manufacturer;

      $country = $this->addCountry($country);
      $manufacturer = Manufacturer::create([
        'title'    => $title,
        'country_code'  => $country->code
      ]);

      return $manufacturer->fresh();
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
