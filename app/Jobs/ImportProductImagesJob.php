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
use App\Models\Product;
use App\Models\SystemEvent;

class ImportProductImagesJob implements ShouldQueue
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
      $this->onQueue('import');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Product::doesntHave('media')->select('id','uuid')->chunk(200, function($products) {
        foreach ($products as $product) {
            $file = Storage::disk('import')->files('/images/'.$product->uuid);
            if (!empty($file)) {
            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/home/vagrant/restoreca/api/storage/app/import/'.$file[0]);
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
}
