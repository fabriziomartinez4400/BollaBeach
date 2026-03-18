<?php

class WooMailerLiteCustomerSyncResetJob extends WooMailerLiteAbstractJob
{
    public function handle($data = [])
    {
        WooMailerLiteCustomer::martUntracked();
        if (isset(static::$jobModel)) {
            static::$jobModel->delete();
        }
        return true;
    }
}