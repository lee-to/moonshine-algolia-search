<?php

declare(strict_types=1);

namespace Leeto\MoonShineAlgoliaSearch\Contracts;

interface HasGlobalAlgoliaSearch
{
    /**
     * @return array{'description': string, 'icon': string, 'image': string}
     */
    public function globalSearch(): array;
}
