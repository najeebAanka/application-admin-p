<?php

namespace App\CPU;

use App\Model\BusinessSetting;
use App\Model\Currency;
use App\Model\Order;
use Illuminate\Support\Carbon;

class BackEndHelper
{
    public static function currency_to_aed($amount)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {
            $default = Currency::find(BusinessSetting::where(['type' => 'system_default_currency'])->first()->value);
            $aed = Currency::where('code', 'AED')->first()->exchange_rate;
            $rate = $default['exchange_rate'] / $aed;
            $value = floatval($amount) / floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return $value;
    }

    public static function usd_to_currency($amount)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {

            if (session()->has('default')) {
                $default = session('default');
            } else {
                $default = Currency::find(Helpers::get_business_settings('system_default_currency'))->exchange_rate;
                session()->put('default', $default);
            }

            if (session()->has('aed')) {
                $aed = session('aed');
            } else {
                $aed = Currency::where('code', 'AED')->first()->exchange_rate;
                session()->put('aed', $aed);
            }

            $rate = $default / $aed;
            $value = floatval($amount) * floatval($rate);
        } else {
            $value = floatval($amount);
        }

        return round($value, 2);
    }

    public static function currency_symbol()
    {
        $currency = Currency::where('id', Helpers::get_business_settings('system_default_currency'))->first();
        return $currency->symbol;
    }

    public static function set_symbol($amount)
    {
        $position = Helpers::get_business_settings('currency_symbol_position');
        if (!is_null($position) && $position == 'left') {
            $string = currency_symbol() . '' . number_format($amount, 2);
        } else {
            $string = number_format($amount, 2) . '' . currency_symbol();
        }
        return $string;
    }

    public static function currency_code()
    {
        $currency = Currency::where('id', Helpers::get_business_settings('system_default_currency'))->first();
        return $currency->code;
    }

    public static function max_earning()
    {
        $data = Order::where(['order_status' => 'delivered'])->select('id', 'created_at', 'order_amount')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $max = 0;
        foreach ($data as $month) {
            $count = 0;
            foreach ($month as $order) {
                $count += $order['order_amount'];
            }
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }

    public static function max_orders()
    {
        $data = Order::select('id', 'created_at')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $max = 0;
        foreach ($data as $month) {
            $count = 0;
            foreach ($month as $order) {
                $count += 1;
            }
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }
}
