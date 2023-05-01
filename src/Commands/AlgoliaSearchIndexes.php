<?php

namespace Leeto\MoonShineAlgoliaSearch\Commands;

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;
use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Menu\MenuItem;
use MoonShine\Menu\MenuSection;
use MoonShine\MoonShine;
use MoonShine\Resources\CustomPage;
use Throwable;

class AlgoliaSearchIndexes extends Command
{
    protected $signature = 'algolia-search:indexes';

    /**
     * @throws MissingObjectId
     */
    public function handle(): int
    {
        $client = SearchClient::create(
            config('algolia.app_id'),
            config('algolia.admin_key')
        );

        $documents = collect();

        foreach (config('moonshine.locales') as $locale) {
            app()->setLocale($locale);

            MoonShine::getMenu()->each(function (MenuSection $groupOrItem) use ($documents) {
                $icon = $this->getDocumentIcon($groupOrItem);

                if ($groupOrItem->isGroup()) {
                    $section = $groupOrItem->label();

                    $groupOrItem->items()->each(function (MenuItem $item) use ($icon, $section, $documents) {
                        $documents->push(
                            $this->extractDocument(
                                $item->page() ?: $item,
                                $item->url(),
                                $item->label(),
                                $icon,
                                $section
                            )
                        );

                        $this->resourceItems($item, $icon, $documents);
                    });
                } else {
                    $documents->push(
                        $this->extractDocument(
                            $groupOrItem->page() ?: $groupOrItem,
                            $groupOrItem->url(),
                            $groupOrItem->label(),
                            $icon
                        )
                    );

                    $this->resourceItems($groupOrItem, $icon, $documents);
                }
            });

            $client
                ->initIndex("moonshine_search_index_$locale")
                ->saveObjects(
                    $documents->toArray()
                );
        }

        return self::SUCCESS;
    }

    private function resourceItems(MenuSection $groupOrItem, string $icon, &$documents): void
    {
        if ($resource = $groupOrItem->resource()) {
            $resourceItems = $resource->query()->get();

            foreach ($resourceItems as $resourceItem) {
                $resource->setItem($resourceItem);
                $icon = $this->getDocumentIcon($resourceItem) ?? $icon;

                $documents->push(
                    $this->extractDocument(
                        $resourceItem,
                        $resource->route('show', $resourceItem->getKey()),
                        $resourceItem->{$resource->titleField() ?? $resourceItem->getKeyName()},
                        $icon
                    )
                );
            }
        }
    }

    private function extractDocument(
        MenuSection|Model|CustomPage $groupOrItem,
        string $url,
        string $title,
        string $icon,
        string $section = ''
    ): array {
        return [
            'objectID' => $this->getDocumentID($groupOrItem),
            'url' => $url,
            'title' => __($title),
            'section' => $section,
            'description' => $this->getDocumentDescription($groupOrItem),
            'icon' => $icon
        ];
    }

    private function getDocumentID(MenuSection|Model|CustomPage $item): string
    {
        if ($item instanceof Model) {
            return str(class_basename($item))
                ->slug('_')
                ->append('_')
                ->append($item->getKey())
                ->value();
        }

        return str($item->url())
            ->remove('//')
            ->after('/')
            ->replace('/', '_')
            ->slug('_')
            ->value();
    }

    private function getDocumentIcon(MenuSection|Model $groupOrItem): string
    {
        if ($groupOrItem instanceof MenuSection) {
            return view('moonshine::components.icon', [
                'icon' => $groupOrItem->iconValue(),
                'color' => 'purple',
                'size' => 6
            ])->render();
        }

        return method_exists($groupOrItem, 'globalSearch')
            ? ($groupOrItem->globalSearch()['image'] ?? '')
            : '';
    }

    private function getDocumentDescription(MenuSection|Model|CustomPage $item): string
    {
        if ($item instanceof CustomPage) {
            try {
                return str(view($item->getView(), $item->getViewData())->render())
                    ->stripTags()
                    ->squish()
                    ->replaceMatches('/\#\#PRE_TL_COMPONENT\#\#.+?\#\#POST_TL_COMPONENT\#\#/', '')
                    ->value();
            } catch (Throwable) {
                return '';
            }
        }

        return method_exists($item, 'globalSearch')
            ? ($item->globalSearch()['description'] ?? '')
            : '';
    }
}
