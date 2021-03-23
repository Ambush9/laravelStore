<?php


namespace App\Services;


use App\Models\Currency;
use Carbon\Carbon;

class CurrencyConversion
{

    public static function convert($sum, $originCurrencyCode='RUB', $targetCurrencyCode = null)
    {
        $originCurrency = Currency::byCode($originCurrencyCode)->first();
        // проверяем что курс обновлялся сегодня

        if ($originCurrency->updated_at->startOfDay() != Carbon::now()->startOfDay()) {
            CurrencyRates::getRates();
//            self::loadContainer();
            $originCurrency = Currency::byCode($originCurrencyCode)->first();
        }

        if(is_null($targetCurrencyCode)) {
            $targetCurrencyCode = session('currency','RUB');
        }
        $targetCurrency = Currency::byCode($targetCurrencyCode)->first();
        if ($targetCurrency->updated_at->startOfDay() != Carbon::now()->startOfDay()) {
            CurrencyRates::getRates();
//            self::loadContainer();
            $targetCurrency = Currency::byCode($targetCurrencyCode)->first();
        }

        return $sum / $originCurrency->rate * $targetCurrency->rate;
    }

    public static function getCurrencySymbol()
    {
        $currency = Currency::byCode(session('currency', 'RUB'))->first();
        return $currency->symbol;
    }

    public static function getBaseCurrency()
    {
        // TODO
    }
}
