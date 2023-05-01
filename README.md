### Algolias search for MoonShine

As a result, all menu items will be indexed, including groups, resources, resource entries and custom pages

### Installation

```shell
composer require lee-to/moonshine-algolia-search
```

Publish config

```shell
php artisan vendor:publish --provider="Leeto\MoonShineAlgoliaSearch\Providers\MoonShineAlgoliaSearchServiceProvider"
```

Register on the https://www.algolia.com, get the keys and set in config `config/algolia.php`

```php
return [
    'app_id' => env('ALGOLIA_APP_ID'),
    'admin_key' => env('ALGOLIA_ADMIN_KEY'),
    'frontend_key' => env('ALGOLIA_FRONTEND_KEY'),
];
```

MoonShine config (app/moonshine.php)

```php
// ...
'header' => 'algolia-search::global-search'
// ...
```

Create indexes

```shell
php artisan algolia-search:indexes
```


If you want to customize fields for models, implement the HasGlobalAlgoliaSearch interface


```php
use Illuminate\Database\Eloquent\Model;
use Leeto\MoonShineAlgoliaSearch\Contracts\HasGlobalAlgoliaSearch;

class Post extends Model
{
    use HasGlobalAlgoliaSearch;
    
    public function globalSearch(): array
    {
        return [
            'description' => $this->text,
            'image' => $this->thumbnail
        ];
    }
}
```
