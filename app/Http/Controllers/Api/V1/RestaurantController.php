<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\RestaurantLogic;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\RegisterCoupon;
use App\Models\Restaurant;
use App\Models\Vendor;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use Illuminate\Support\Facades\DB;


class RestaurantController extends Controller
{
    public function get_restaurants(Request $request, $filter_data = "all")
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => 'Zone id is required!']);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $restaurants = RestaurantLogic::get_restaurants($request['limit'], $request['offset'], $zone_id, $filter_data);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting($restaurants['restaurants'], true);

        return response()->json($restaurants, 200);
    }

    public function get_latest_restaurants(Request $request, $filter_data = "all")
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => 'Zone id is required!']);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $restaurants = RestaurantLogic::get_latest_restaurants($request['limit'], $request['offset'], $zone_id, $filter_data);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting($restaurants['restaurants'], true);

        return response()->json($restaurants['restaurants'], 200);
    }

    public function get_popular_restaurants(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => 'Zone id is required!']);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $restaurants = RestaurantLogic::get_popular_restaurants($request['limit'], $request['offset'], $zone_id);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting($restaurants['restaurants'], true);

        return response()->json($restaurants['restaurants'], 200);
    }

    public function get_details($id)
    {
        $restaurant = RestaurantLogic::get_restaurant_details($id);
        if ($restaurant) {
            $category_ids = DB::table('food')
                ->join('categories', 'food.category_id', '=', 'categories.id')
                ->selectRaw('IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
                ->where('food.restaurant_id', $id)
                ->where('categories.status', 1)
                ->groupBy('categories')
                ->get();
            // dd($category_ids->pluck('categories'));
            $restaurant = Helpers::restaurant_data_formatting($restaurant);
            $restaurant['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());
        }
        return response()->json($restaurant, 200);
    }

    public function get_searched_restaurants(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => 'Zone id is required!']);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id = $request->header('zoneId');
        $restaurants = RestaurantLogic::search_restaurants($request['name'], $zone_id, $request['limit'], $request['offset']);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting($restaurants['restaurants'], true);
        return response()->json($restaurants, 200);
    }

    public function reviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $id = $request['restaurant_id'];


        $reviews = Review::with(['customer', 'food'])
            ->whereHas('food', function ($query) use ($id) {
                return $query->where('restaurant_id', $id);
            })
            ->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            $item['food_name'] = $item->food->name;
            unset($item['food']);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }

    public static function calcCoordinates($longitude, $latitude, $radius = 20)
    {
        $lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
        $lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
        $lat_min = $latitude - ($radius / 69);
        $lat_max = $latitude + ($radius / 69);

        return [
            'min' => [
                'lat' => $lat_min,
                'lng' => $lng_min,
            ],
            'max' => [
                'lat' => $lat_max,
                'lng' => $lng_max,
            ],
        ];
    }

    public function scopeDistance($from_latitude, $from_longitude, $distance)
    {
        $between_coords = $this->calcCoordinates($from_longitude, $from_latitude, $distance);

        return Restaurant::where('status', 1)
            ->where(function ($q) use ($between_coords) {
                $q->whereBetween('longitude', [$between_coords['min']['lng'], $between_coords['max']['lng']]);
            })
            ->where(function ($q) use ($between_coords) {
                $q->whereBetween('latitude', [$between_coords['min']['lat'], $between_coords['max']['lat']]);
            })->get();
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'restaurant_phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:restaurants',
            'minimum_delivery_time' => 'required|regex:/^([0-9]{2})$/|min:2|max:2',
            'maximum_delivery_time' => 'required|regex:/^([0-9]{2})$/|min:2|max:2',
            'logo' => 'required',
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:vendors',
            'password' => 'required|confirmed|min:6',
        ], [
            'f_name.required' => 'First name is required!'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->save();

        $restaurant = new Restaurant;
        $restaurant->name = $request->name;
        $restaurant->phone = $request->phone;
        $restaurant->email = $request->email;
        $restaurant->logo = Helpers::upload('restaurant/', 'png', $request->file('logo'));
        $restaurant->cover_photo = Helpers::upload('restaurant/cover/', 'png', $request->file('cover_photo'));
        $restaurant->address = $request->address;
        $restaurant->latitude = $request->latitude;
        $restaurant->longitude = $request->longitude;
        $restaurant->vendor_id = $vendor->id;
        $restaurant->tax = 0;
        $restaurant->status = 0;
        $restaurant->status = 0;
        $restaurant->zone_id = 1;
        $restaurant->restaurant_phone = $request->restaurant_phone;
        $restaurant->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time;
        $restaurant->Profit_Ratio =BusinessSetting::where('key','Profit_Ratio')->first()->value;
        $restaurant->save();

        $msg="Restaurant registered successfully";

        return response()->json($msg , 200);    }

    // public function get_product_rating($id)
    // {
    //     try {
    //         $product = Food::find($id);
    //         $overallRating = ProductLogic::get_overall_rating($product->reviews);
    //         return response()->json(floatval($overallRating[0]), 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['errors' => $e], 403);
    //     }
    // }

}
