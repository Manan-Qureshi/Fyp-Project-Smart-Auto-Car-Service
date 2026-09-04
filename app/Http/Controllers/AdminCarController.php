<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarType;
use App\Models\CarModel;
use App\Models\Booking;

class AdminCarController extends Controller
{
    public function index()
    {
        try {
            $types = CarType::with('models')->get();
        } catch (\Exception $e) {
            $types = collect();
        }
        return view('admin.cars.index', compact('types'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        CarType::create($request->all());

        return back()->with('success', 'Car Company Added Successfully');
    }

    public function destroyType(CarType $type)
    {
        // Check if any active (non-completed, non-cancelled) bookings exist for any model of this company
        $hasActiveBookings = Booking::whereIn('car_model_id', $type->models()->pluck('id'))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'A booked car company cannot be deleted.');
        }

        $type->models()->delete();
        $type->delete();

        return back()->with('success', 'Car Company Deleted Successfully');
    }

    public function storeModel(Request $request)
    {
        $request->validate([
            'car_type_id' => 'required|exists:car_types,id',
            'name' => 'required|string|max:255',
            'price_modifier' => 'required|numeric|min:0'
        ]);

        CarModel::create($request->all());

        return back()->with('success', 'Car Model Added Successfully');
    }

    public function destroyModel(CarModel $model)
    {
        // Check if any active (non-completed, non-cancelled) bookings exist for this model
        $hasActiveBookings = Booking::where('car_model_id', $model->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'A booked car model cannot be deleted.');
        }

        $model->delete();

        return back()->with('success', 'Car Model Deleted Successfully');
    }
}
