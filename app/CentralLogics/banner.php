<?php

namespace App\CentralLogics;

use App\Models\Banner;
use App\Models\Food;
use App\Models\Restaurant;
use App\CentralLogics\Helpers;

class BannerLogic
{

    public static function get_banners($zone_id)
    {
        $banners = Banner::active()->where('zone_id', $zone_id)->where('ad_show', 1)->inRandomOrder()->take(5)->get();
        $data = [];
        foreach($banners as $banner)
        {
            if($banner->type=='restaurant_wise')
            {
                $restaurant = Restaurant::find($banner->data);
                $data[]=[
                    'id'=>$banner->id,
                    'title'=>$banner->title,
                    'type'=>$banner->type,
                    'ads_type'=>$banner->ads_type,
                    'type_count'=>$banner->type_count,
                    'start_date'=>$banner->start_date,
                    'end_date'=>$banner->end_date,
                    'image'=>$banner->image,
                    'restaurant'=> $restaurant?Helpers::restaurant_data_formatting($restaurant, false):null,
                    'food'=>null
                ];
            }
            if($banner->type=='item_wise')
            {
                $food = Food::find($banner->data);
                $data[]=[
                    'id'=>$banner->id,
                    'title'=>$banner->title,
                    'type'=>$banner->type,
                    'ads_type'=>$banner->ads_type,
                    'type_count'=>$banner->type_count,
                    'start_date'=>$banner->start_date,
                    'end_date'=>$banner->end_date,
                    'image'=>$banner->image,
                    'restaurant'=> null,
                    'food'=> $food?Helpers::product_data_formatting($food, false):null,
                ];
            }
        }
        return $data;
    }
}
