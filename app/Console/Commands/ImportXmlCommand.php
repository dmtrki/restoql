<?php

namespace App\Console\Commands;

use App\Jobs\ImportProductAttributesJob;
use App\Jobs\ImportProductCategoriesJob;
use App\Jobs\ImportProductImagesJob;
use App\Jobs\ImportProductsJob;
use App\Jobs\ProcessXmlToDbJob;
use App\Jobs\UpdateProductPricesJob;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use XML;

class ImportXmlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restoreca:import {filename?} {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starting xml import jobs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): Int
    {
      $filename = $this->argument('filename') ?? $this->getTodayFilename();
      $path = \Storage::disk('import')->path($filename);

      $type = $this->argument('type') ?? 'prices';

      switch ($type) {
        case 'prices':
          $this->info('Обновление цен');
          UpdateProductPricesJob::dispatch();
          break;

        case 'process':
          $this->info('Импорт xml в бд и импорт категорий');
          ImportProductCategoriesJob::dispatch($path);
          ProcessXmlToDbJob::dispatch($path);
          break;

        case 'products':
          $this->info('Добавление товаров из записанной в базу хмлки');
          ImportProductsJob::dispatch();
          break;

        case 'images':
          $this->info('Добавление фоток к товарам');
          ImportProductImagesJob::dispatch();
          break;

        case 'attrs':
          $this->info('Добавление аттрибутов к товарам');
          ImportProductAttributesJob::dispatch();
          break;
      }

      $this->info('Работы по импорту начаты');
      return 1;
    }

    public function getTodayFilename(): string
    {
      $now = Carbon::now();
      return $now->format('d-m-y').'.xml';
    }
}
