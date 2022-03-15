<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use XML;

class ImportBaseJob implements ShouldQueue, ShouldBeUnique
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $pathToday;
  protected $now;

  public function __construct($path = false)
  {
    $this->onQueue('import');
    $this->now = Carbon::now();

    if (!$path) {
      $filename = $this->now->format('d-m-y').'.xml';
      $this->pathToday =\Storage::disk('import')->path($filename);
    } else {
      $this->pathToday = $path;
    }
  }

  protected function getXmlFileContent()
  {
    try {
      $xml = XML::import($this->pathToday)->get();
    } catch (\Exception $exception) {
      error_log('getXmlFileContent');
      error_log($exception);
      $xml = false;
    }

    return $xml;
  }

}
