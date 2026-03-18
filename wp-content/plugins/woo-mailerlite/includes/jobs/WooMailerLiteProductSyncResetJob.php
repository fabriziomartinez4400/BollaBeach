<?php

class WooMailerLiteProductSyncResetJob extends WooMailerLiteAbstractJob
{
    public function handle($data = [])
    {
        $products = WooMailerLiteProduct::tracked()->get(100);
        if ($products->hasItems()) {
            foreach ($products->items as $product) {
                $product->tracked = false;
                $product->ignored = false;
                $product->save();
            }
        }
        if (isset(self::$jobModel)) {
            self::$jobModel->delete();
        }
        if (WooMailerLiteProduct::getTrackedProductsCount()) {
            self::dispatchSync();
        } else {
            WooMailerLiteCategorySyncResetJob::dispatchSync();
        }
    }
}
