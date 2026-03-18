<?php

class WooMailerLiteAdminSyncController extends WooMailerLiteController
{
    public function sync()
    {
        $this->authorize();
        $alreadyStarted = WooMailerLiteCache::get('manual_sync', false);
        if (!$alreadyStarted) {
            WooMailerLiteCache::set('manual_sync', true, 18000);
        }

        // save count in cache
        $untrackedCategories = WooMailerLiteCategory::getUntrackedCategoriesCount();
        $untrackedProducts = WooMailerLiteProduct::getUntrackedProductsCount();
        $untrackedCustomers = WooMailerLiteCustomer::getUntrackedCustomersCount();
        WooMailerLiteCache::set('resource_sync_counts', [
            'categories' => $untrackedCategories,
            'products' => $untrackedProducts,
            'customers' => $untrackedCustomers,
        ], 18000);
        $totalUntrackedResources = $untrackedCategories + $untrackedProducts + $untrackedCustomers;
        if ($totalUntrackedResources == 0) {
            WooMailerLiteCache::delete('manual_sync');
            return $this->response('no resources to sync', 200);
        }

        WooMailerLiteCategorySyncJob::dispatch();

        return $this->response([
            'message' => 'Sync in progress',
            'data' => [
                'totalUntrackedResources' => $totalUntrackedResources
            ]
        ], function_exists('as_enqueue_async_action') ? 203 : 202);
    }

    public function resetSync()
    {
        $this->authorize();
        WooMailerLiteCache::delete('manual_sync');
        WooMailerLiteProductSyncResetJob::dispatchSync();
        return $this->response(['message' => 'reset sync completed', 'data' => [
            'totalUntrackedResources' => WooMailerLiteCategory::getUntrackedCategoriesCount() + WooMailerLiteProduct::getUntrackedProductsCount() +  WooMailerLiteCustomer::getUntrackedCustomersCount()
        ]], 202);
    }
}
