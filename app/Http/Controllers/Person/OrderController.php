<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // получаем заказы для конкретого пользователя
        $orders = Auth::user()->orders()->where('status', 1)->paginate(10);
        return view('auth.orders.index', compact('orders'));
    }

    public function show(Order $order) {
        // если нет такого заказа, то возвращаем назад
        if(!Auth::user()->orders->contains($order)) {
            return back();
        }
        return view('auth.orders.show', compact('order'));
    }
}
