<?php

namespace App\Classes;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Basket
{
    protected $order;

    /**
     * Basket constructor.
     * @param $order
     */
    public function __construct($createOrder = false)
    {
        $orderId = session('orderId');
        // если нет, то создаем и кладем в сессию
        if (is_null($orderId) && $createOrder) {
            $data = [];
            if (Auth::check()) {
                $data['user_id'] = Auth::id();
            }

            $this->order = Order::create($data);
            session(['orderId' => $this->order->id]);
        } else {
            $this->order = Order::findOrFail($orderId);
        }
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function countAvailable() {
        foreach ($this->order->products as $orderProduct) {
            if ($orderProduct->count < $this->getPivotRow($orderProduct->id)->count ) {
                return false;
            }
        }
        return true;
    }

    public function saveOrder($name, $phone)
    {
        if(!$this->countAvailable()) {
            return false;
        }
        return $this->order->saveOrder($name, $phone);
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);

        // проверка что товар уже есть в корзине
        if ($this->order->products->contains($productId)) {
            // получаем связанную запись
            $pivotRow = $this->getPivotRow($productId);

            // если такой товар уже есть в корзине, то увеличиваем кол-во
            $pivotRow->count++;
            // проверка на кол-во товара
            if($pivotRow->count > $product->count) {
                return false;
            }
            $pivotRow->update();
        } else {
            if($product->count === 0) {
                return false;
            }
            // это использование связи(по имени функции связи в модели)
            $this->order->products()->attach($productId);
        }

        Order::changeFullSum($product->price);

        return true;
    }

    public function removeProduct($productId)
    {
        if ($this->order->products->contains($productId)) {
            // получаем связанную запись
            $pivotRow = $this->getPivotRow($productId);
            if ($pivotRow->count < 2) {
                // если товар есть и он один в коллекции текущей корзины, то удаляем товар из таблицы связей
                $this->order->products()->detach($productId);
            } else {
                $pivotRow->count--;
                $pivotRow->update();
            }
        }

        $product = Product::find($productId);
        Order::changeFullSum(-$product->price);
    }

    protected function getPivotRow($productId) {
        return $this->order->products()->where('product_id', $productId)->first()->pivot;
    }
}
