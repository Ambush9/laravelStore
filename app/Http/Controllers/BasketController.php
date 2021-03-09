<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasketController extends Controller
{
    public function basket () {
        $orderId = session('orderId');
        if (!is_null($orderId)) {
            $order = Order::findOrFail($orderId);
        }

        return view('basket', compact('order'));
    }

    public function basketPlace () {
        $orderId = session('orderId');
        if (is_null($orderId)) {
            return redirect()->route('index');
        }
        $order = Order::find($orderId);
        return view('order', compact('order'));
    }

    public function basketAdd ($productId)
    {
        // смотрим есть ли в сессии нужный ключ
        $orderId = session('orderId');
        // если нет, то создаем и кладем в сессию
        if (is_null($orderId)) {
            $order = Order::create();
            session(['orderId' => $order->id]);
        } else {
            $order = Order::find($orderId);
        }

        // проверка что товар уже есть в корзине
        if($order->products->contains($productId)) {
            // получаем связанную запись
            $pivotRow = $order->products()->where('product_id', $productId)->first()->pivot;

            // если такой товар уже есть в корзине, то увеличиваем кол-во
            $pivotRow->count++;
            $pivotRow->update();
        } else {
            // это использование связи(по имени функции связи в модели)
            $order->products()->attach($productId);
        }

        // если юзер авторизован, сохраняем в БД
        if(Auth::check()) {
            $order->user_id = Auth::id();
            $order->save();
        }

        $product = Product::find($productId);
        Order::changeFullSum($product->price);

        session()->flash('success', 'Добавлен товар' . $product->name);

        // т.о при перезагрузке страницы не дублируются записи в корзине
        return redirect()->route('basket');
    }

    public function basketRemove($productId)
    {
        $orderId = session('orderId');
        if (is_null($orderId)) {
            return redirect()->route('basket');
        }

        $order = Order::find($orderId);
        if($order->products->contains($productId)) {
            // получаем связанную запись
            $pivotRow = $order->products()->where('product_id', $productId)->first()->pivot;
            if($pivotRow->count < 2) {
                // если товар есть и он один в коллекции текущей корзины, то удаляем товар из таблицы связей
                $order->products()->detach($productId);
            } else {
                $pivotRow->count--;
                $pivotRow->update();
            }
        }

        $product = Product::find($productId);
        Order::changeFullSum(-$product->price);

        return redirect()->route('basket');
    }

    public function basketConfirm(Request $request) {
        $orderId = session('orderId');
        if (is_null($orderId)) {
            return redirect()->route('index');
        }

        $order = Order::find($orderId);
        // сохраняем заказ
        $success = $order->saveOrder($request->name, $request->phone);

        if ($success) {
            session()->flash('success', 'Ваш заказ принят в обработку!');
        } else {
            session()->flash('warning', 'Случилась ошибка!');
        }

        Order::eraseOrderSum();

        return redirect()->route('index');
    }
}
