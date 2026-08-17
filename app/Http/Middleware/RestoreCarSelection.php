<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\CarModel;

class RestoreCarSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('selected_car_model') && $request->hasCookie('selected_car_model_id')) {
            $carModelId = $request->cookie('selected_car_model_id');
            $carModel = CarModel::with('carType')->find($carModelId);
            
            if ($carModel) {
                session([
                    'selected_car_model' => [
                        'id'             => $carModel->id,
                        'name'           => $carModel->name,
                        'type_name'      => optional($carModel->carType)->name ?? 'Car',
                        'price_modifier' => $carModel->price_modifier,
                    ]
                ]);
            }
        }

        return $next($request);
    }
}
