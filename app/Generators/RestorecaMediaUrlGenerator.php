<?php
namespace App\Generators;

use DateTimeInterface;
use App\Models\Media;
use App\Generators\RestorecaMediaPathGenerator as PathGenerator;
use Spatie\MediaLibrary\UrlGenerator\BaseUrlGenerator;

class RestorecaMediaUrlGenerator extends BaseUrlGenerator
{
  protected $pathGenerator = App\Generators\RestorecaMediaPathGenerator::class;
  

  public function getUrl() : string
  {
    return 'https://restoreca.ru/media/'.$this->getPathRelativeToRoot();
  }

  public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
  {
      return $this->getUrl();
  }

  public function getResponsiveImagesDirectoryUrl(): string
  {
    return $this->getUrl();
  }
}