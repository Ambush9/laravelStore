<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    // связь один-ко-многим, использует таблицу order_product
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('count')->withTimestamps();
    }

//    public function user() {
//        return $this->belongsTo(User::class);
//    }

    public function calculateFullSum()
    {
        $sum = 0;
        foreach ($this->products()->withTrashed()->get() as $product) {
            $sum+= $product->getPriceForCount();
        }
        return $sum;
    }

    public static function eraseOrderSum()
    {
        session()->forget('full_order_sum');
    }

    // $this - там лежат все товары
    public static function getFullSum() {
        return session('full_order_sum', 0);
    }

    public static function changeFullSum($changeSum)
    {
        $sum = self::getFullSum() + $changeSum;
        session(['full_order_sum' => $sum]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // сохраняет заказ
    public function saveOrder($name, $phone)
    {
        if($this->status === 0) {
            // записали в БД данные заявки, введенные пользователем($request - массив со всеми post параметрами)
            $this->name = $name;
            $this->phone = $phone;
            $this->status = 1;
            $this->save();
            // убираем заказ из сессии
            session()->forget('orderId');

            return true;
        } else {
            return false;
        }

    }
}
