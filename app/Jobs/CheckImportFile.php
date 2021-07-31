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
use Illuminate\Filesystem\Filesystem;
use Spatie\MediaLibrary\Models\Media;
use Carbon\Carbon;

class CheckImportFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
      DB::table('import_nodes')->truncate();

      $now = Carbon::now();
      $todayFileName = $now->format('d-m-y').'.xml';
      $pathToToday = storage_path('app/'.$todayFileName);
      if (Storage::disk('import')->missing($todayFileName)) return;
      if (!file_exists($pathToToday)) {
        $importFile = Storage::disk('import')->get($todayFileName);
        Storage::put($todayFileName, $importFile);
      }
    }
}
