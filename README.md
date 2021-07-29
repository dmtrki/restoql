# restoreca gql api


## установленные пакеты
-  https://backpackforlaravel.com/docs/4.1/
- https://github.com/spatie/laravel-sluggable
- https://github.com/michaeldyrynda/laravel-efficient-uuid
- https://github.com/michaeldyrynda/laravel-model-uuid
- https://github.com/lazychaser/laravel-nestedset
- https://github.com/spatie/laravel-medialibrary
- https://github.com/laravel/telescope
- https://github.com/spatie/laravel-ray


### laravel-efficient-uuid
```php
$table->efficientUuid('uuid')->index();
```
---
```php
  protected $casts = [
    'uuid' => EfficientUuid::class,
  ];
```

### laravel-model-uuid
```php
// Find a specific post with the default (uuid) column name
$post = Post::whereUuid($uuid)->first();

// Find multiple posts with the default (uuid) column name
$post = Post::whereUuid([$first, $second])->get();

// Find a specific post with a custom column name
$post = Post::whereUuid($uuid, 'custom_column')->first();

// Find multiple posts with a custom column name
$post = Post::whereUuid([$first, $second], 'custom_column')->get();
```
---
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;

class Post extends Model
{
    use GeneratesUuid;
}
```
---
```php
class Post extends Model
{
    public function uuidColumn(): string
    {
        return 'custom_column';
    }
}
```

### laravel-slugable

```php
  use Spatie\Sluggable\HasSlug;
  use Spatie\Sluggable\SlugOptions;

class Model {
  use HasSlug;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
```
---
```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->extraScope(fn ($builder) => $builder->where('scope_id', $this->scope_id));
}
```

### laravel-nestedset
```php
use Kalnoy\Nestedset\NestedSet;

Schema::create('table', function (Blueprint $table) {
    ...
    NestedSet::columns($table);
});
```
---
```php
use Kalnoy\Nestedset\NodeTrait;

class Foo extends Model {
    use NodeTrait;
}
```
---
```php
$node = MenuItem::findOrFail($id);
$node->siblings()->withDepth()->get(); // OK

$nodes = Category::get()->toFlatTree();

$result = Category::whereDescendantOf($node)->get();
$result = Category::whereNotDescendantOf($node)->get();
$result = Category::orWhereDescendantOf($node)->get();
$result = Category::orWhereNotDescendantOf($node)->get();
$result = Category::whereDescendantAndSelf($id)->get();

// Include target node into result set
$result = Category::whereDescendantOrSelf($node)->get();

$result = Category::whereAncestorOf($node)->get();
$result = Category::whereAncestorOrSelf($id)->get();

$root = Category::descendantsAndSelf($rootId)->toTree()->first();
$tree = Category::descendantsOf($rootId)->toTree($rootId);

```