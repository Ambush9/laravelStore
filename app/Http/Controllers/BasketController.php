<?php

namespace App\Http\Controllers;

use App\Classes\Basket;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasketController extends Controller
{
    public function basket()
    {
        $order = (new Basket())->getOrder();

        return view('basket', compact('order'));
    }

    public function basketConfirm(Request $request)
    {
        $email = Auth::check() ? Auth::user()->email : $request->email;

        // сохраняем заказ
        $status = (new Basket())->saveOrder($request->name, $request->phone, $email);

        if ($status) {
            session()->flash('success', 'Ваш заказ принят в обработку!');
        } else {
            session()->flash('warning', 'Товар недоступен для заказа в полном объеме');
        }

        Order::eraseOrderSum();

        return redirect()->route('index');
    }

    public function basketPlace()
    {
        $basket = (new Basket());
        $order = $basket->getOrder();
        if(!$basket->countAvailable()) {
            session()->flash('warning', 'Товар недоступен для заказа в полном объеме');
            return redirect()->route('basket');
        }

        return view('order', compact('order'));
    }

    public function basketAdd($productId)
    {
        $result = (new Basket(true))->addProduct($productId);

        if($result) {
            session()->flash('success', 'Товар добавлен в корзину');
        } else {
            session()->flash('warning', 'Товар недоступен в большем кол-ве для заказа');
        }

        // т.о при перезагрузке страницы не дублируются записи в корзине
        return redirect()->route('basket');
    }

    public function basketRemove($productId)
    {
        (new Basket())->removeProduct($productId);

        session()->flash('warning', 'Товар удален');

        return redirect()->route('basket');
    }
}
