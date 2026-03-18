<?php

class WooMailerLiteCategorySyncResetJob extends WooMailerLiteAbstractJob
{
    public function handle($data = [])
    {
        $categories = WooMailerLiteCategory::tracked()->get(100);
        if ($categories->hasItems()) {
            foreach ($categories->items as $category) {
                $category->tracked = false;
                $category->save();
            }
        }
        if (isset(self::$jobModel)) {
            self::$jobModel->delete();
        }
        if (WooMailerLiteCategory::getTrackedCategoriesCount()) {
            self::dispatchSync();
        } else {
            WooMailerLiteCustomerSyncResetJob::dispatchSync();
        }
    }
}