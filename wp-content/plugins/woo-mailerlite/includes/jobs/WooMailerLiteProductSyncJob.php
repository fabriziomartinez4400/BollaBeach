<?php

class WooMailerLiteProductSyncJob extends WooMailerLiteAbstractJob
{
    public function handle($data = [])
    {
        $products = WooMailerLiteProduct::untracked()->get(100);
        $syncProducts = [];
        if (!$products->hasItems()) {
            WooMailerLiteCustomerSyncJob::dispatch($data);
            return;
        }

        $countInCache = WooMailerLiteCache::get('resource_sync_counts', false);
        if (isset($countInCache['products'])) {
            $countInCache = $countInCache['products'];
        }

        foreach ($products->items as $product) {

            if ((trim($product->url) === '') || !is_string($product->url)) {
                continue;
            }
            if (!$product->name) {
                $productObj = wc_get_product( $product->resource_id );
                $productName = $productObj->get_name();
                if (!$productName) {
                    $product->tracked = true;
                    $product->save();
                    continue;
                }
                $product->name = $productName;
                $product->price = $productObj->get_price();
            }
            if (!is_string($product->description)) {
                $product->description = '';
            }
            if ($product->description !== '' && ctype_space($product->description)) {
                $product->description = '';
            }


            $syncProducts[] = array_filter([
                'resource_id' => (string)$product->resource_id,
                'name' => $product->name,
                'price' => $product->price,
                'url' => $product->url,
                'exclude_from_automations' => (bool)$product->ignored,
                'categories' => $product->category_ids,
                'image' => $product->image ?? null,
                'description' => $product->description ?? null,
                'short_description' => $product->short_description ?? null,
            ]);

            $product->tracked = true;
            $product->save();
        }

        if (!empty($syncProducts)) {
            WooMailerLiteApi::client()->importProducts($syncProducts);
            if (WooMailerLiteProduct::getUntrackedProductsCount() < $countInCache) {
                static::dispatch($data);
            }
        } else {
            WooMailerLiteCustomerSyncJob::dispatch($data);
        }
    }
}
