<?php

class WooMailerLiteCategorySyncJob extends WooMailerLiteAbstractJob
{
    public function handle($data = [])
    {
        $categories = WooMailerLiteCategory::untracked()->get(100);
        if (!$categories->hasItems()) {
            WooMailerLiteProductSyncJob::dispatch($data);
            return;
        }
        $countInCache = WooMailerLiteCache::get('resource_sync_counts', false);
        if (isset($countInCache['categories'])) {
            $countInCache = $countInCache['categories'];
        }

        $importCategories = [];

        foreach ($categories->items as $category) {
            $importCategories[] = [
                'name' => $category->name,
                'resource_id' => (string)$category->resource_id,
            ];

            $category->tracked = true;
            $category->save();
        }

        if (!empty($importCategories)) {
            WooMailerLiteApi::client()->importCategories($importCategories);
            if (WooMailerLiteCategory::getUntrackedCategoriesCount() < $countInCache) {
                static::dispatch($data);
            }
        } else {
            WooMailerLiteProductSyncJob::dispatch($data);
        }
    }
}
