<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddShopRequest;
use App\Http\Requests\ShowShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Add a new store
     */
    public function store(AddShopRequest $request): ShopResource
    {
        $attributes = $request->validated();

        $record = [
            'name' => $attributes['name'],
            'geo_coordinates' => DB::raw("ST_SRID(POINT({$attributes['latitude']}, {$attributes['longitude']}), 4326)"),
            'status' => $attributes['status'],
            'store_type' => $attributes['store_type'],
            'max_delivery_distance' => $attributes['max_delivery_distance'],
            'created_at' => now(),
        ];

        $shopId = Shop::insertGetId($record);

        $responseData = Shop::selectRaw('
            id,
            name,
            ST_X(geo_coordinates) as longitude,
            ST_Y(geo_coordinates) as latitude,
            status,
            store_type,
            max_delivery_distance,
            created_at,
            updated_at'
        )->where('id', $shopId)->firstOrFail();

        return new ShopResource($responseData);
    }

    /**
     * Find a stores within the given distance
     */
    public function show(ShowShopRequest $request): AnonymousResourceCollection
    {
        $attributes = $request->validated();

        $query = 'SELECT id, name, ST_Distance_Sphere(
                        POINT(?, ?) ,
                        POINT(ST_X(geo_coordinates), ST_Y(geo_coordinates))
                    ) * .000621371192 as distance_in_miles
                 FROM shops
                 HAVING `distance_in_miles` BETWEEN 0 and ?
                 ORDER BY distance_in_miles';

        // Use prepared statements to prevent SQL injection (security)
        $result = DB::select($query, [$attributes['longitude'], $attributes['latitude'], $attributes['distance']]);

        $nearestShop = Shop::findMany(collect($result)->pluck('id'));

        return ShopResource::collection($nearestShop);
    }
}
