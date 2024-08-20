<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddShopRequest;
use App\Http\Requests\SearchShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Postcode;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Add a new store
     *
     * @unauthenticated
     *
     * @group Stores API endpoints
     *
     * @responseFile 200 storage/responses/api/stores/store.post.json
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
     * Search for stores within a specified distance (in miles).
     *
     * @unauthenticated
     *
     * @group Stores API endpoints
     *
     * @responseFile 200 storage/responses/api/stores/get.post.json
     */
    public function search(SearchShopRequest $request): AnonymousResourceCollection
    {
        $attributes = $request->validated();

        $distance = $request->filled('distance') ? $attributes['distance'] : 10;

        $shopCoordinates = Postcode::selectRaw(
            '
                ST_X(geo_coordinates) as longitude,
                ST_Y(geo_coordinates) as latitude
            '
        )->where('postcode', $attributes['postcode'])->firstOrFail();

        $query = '
                id,
                name,
                status,
                max_delivery_distance,
                created_at,
                updated_at,
                ST_X(geo_coordinates) as longitude,
                ST_Y(geo_coordinates) as latitude,
                ST_Distance_Sphere(
                    POINT(?, ?) ,
                    POINT(ST_X(geo_coordinates), ST_Y(geo_coordinates))
                ) * .000621371192 as distance_in_miles
        ';

        $nearestShops = Shop::selectRaw(
            $query,
            [$shopCoordinates->longitude, $shopCoordinates->latitude]
        )
            ->where('status', Shop::OPEN_STATUS)
            ->havingRaw('distance_in_miles BETWEEN 0 and ?', [$distance])
            ->orderByRaw('distance_in_miles')
            ->paginate();

        return ShopResource::collection($nearestShops);
    }
}
