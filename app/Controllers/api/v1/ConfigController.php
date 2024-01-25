<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\CPU\ProductManager;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use App\Model\Brand;
use App\Model\Category;
use App\Model\Color;
use App\Model\Currency;
use App\Model\HelpTopic;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function configuration()
    {
        $currency = Currency::where('status', 1)->get();
        $social_login = [];
        foreach (Helpers::get_business_settings('social_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (boolean)$social['status']
            ];
            array_push($social_login, $config);
        }

        $languages = Helpers::get_business_settings('pnc_language');
        $lang_array = [];
        foreach ($languages as $language) {
            array_push($lang_array, [
                'code' => $language,
                'name' => Helpers::get_language_name($language)
            ]);
        }

        $main_banners = \App\Resources\Banner::collection(Banner::where('banner_type', 'Main Banner')->where(['published' => 1])->get());
        $footer_banners = \App\Resources\Banner::collection(Banner::where('banner_type', 'Footer Banner')->where(['published' => 1])->get());
        $offer_banners = \App\Resources\Banner::collection(Banner::where('banner_type', 'Offer Banner')->where(['published' => 1])->get());
        $brands = \App\Resources\Brand::collection(Brand::where(['status' => 1])->get());
        $categories_home_top = \App\Resources\Category::collection(Category::where('parent_id', 0)->where('home_top', 1)->get());
        $categories_home = \App\Resources\Category::collection(Category::where('parent_id', 0)->where('home_status', 1)->get());

        return Helpers::sendSuccess('Data Got!', [
            'categories_home_top' => $categories_home_top,
            'categories_home' => $categories_home,
            'brands' => $brands,
            'banners' => [
                'main_banners' => $main_banners,
                'footer_banners' => $footer_banners,
                'offer_banners' => $offer_banners,
            ],
            'system_default_currency' => (int)Helpers::get_business_settings('system_default_currency'),
            'digital_payment' => (boolean)Helpers::get_business_settings('digital_payment')['status'] ?? 0,
            'cash_on_delivery' => (boolean)Helpers::get_business_settings('cash_on_delivery')['status'] ?? 0,
            'about_us' => Helpers::get_business_settings('about_us'),
            'privacy_policy' => Helpers::get_business_settings('privacy_policy'),
            'faq' => HelpTopic::all(),
            'terms_and_conditions' => Helpers::get_business_settings('terms_condition'),
            'currency_list' => $currency,
            'currency_symbol_position' => Helpers::get_business_settings('currency_symbol_position') ?? 'right',
            'maintenance_mode' => (boolean)Helpers::get_business_settings('maintenance_mode') ?? 0,
            'language' => $lang_array,
            'colors' => Color::all(),
            'unit' => Helpers::units(),
            'shipping_method' => Helpers::get_business_settings('shipping_method'),
            'email_verification' => (boolean)Helpers::get_business_settings('email_verification'),
            'phone_verification' => (boolean)Helpers::get_business_settings('phone_verification'),
            'country_code' => Helpers::get_business_settings('country_code'),
            'social_login' => $social_login,
            'currency_model' => Helpers::get_business_settings('currency_model'),
            'forgot_password_verification' => Helpers::get_business_settings('forgot_password_verification'),
//            'announcement' => Helpers::get_business_settings('announcement'),
        ]);
    }
}

