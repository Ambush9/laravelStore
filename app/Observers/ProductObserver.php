<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Subscription;

class ProductObserver
{
    public function updating(Product $product)
    {
        $oldCount = $product->getOriginal('count'); // получает выбранное значение до изменения

        // если изменили кол-во товара, которого не было в наличии, отправляем письмо подписанным на продукт юзерам
        if($oldCount == 0 && $product->count > 0) {
            Subscription::sendEmailBySubscription($product);
        }

    }
}
