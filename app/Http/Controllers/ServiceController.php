<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarModel;
use App\Models\Service;
use App\Models\CarType;
use App\Models\TimeDuration;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    // -------------------------------------------------------
    // Admin CRUD for global service catalog
    // -------------------------------------------------------
    public function index()
    {
        $services = Service::orderBy('name')->get();
        $editService = request('edit') ? Service::find(request('edit')) : null;
        $durations = TimeDuration::ordered();
        $editDuration = request('edit_duration') ? TimeDuration::find(request('edit_duration')) : null;
        $serviceCategories = ServiceCategory::orderBy('name')->get();

        return view('admin.services.index', compact('services', 'editService', 'durations', 'editDuration', 'serviceCategories'));
    }

    public function create()
    {
        $carTypes = CarType::with('models')->get();
        return view('admin.services.create', compact('carTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'nullable|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'car_type_id' => 'nullable|exists:car_types,id',
            'car_model_id' => 'nullable|exists:car_models,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($validated);
        return redirect('/admin/services')->with('success', 'Service created successfully.');
    }

    // -------------------------------------------------------
    // Time Duration CRUD
    // -------------------------------------------------------
    public function storeDuration(Request $request)
    {
        $data = $request->validate([
            'minutes' => 'required|integer|min:5|max:480|unique:time_durations,minutes',
            'label' => 'required|string|max:100',
        ]);
        TimeDuration::create($data);
        return redirect('/admin/services')->with('success', 'Duration added.');
    }

    public function destroyDuration(TimeDuration $duration)
    {
        $duration->delete();
        return redirect('/admin/services')->with('success', 'Duration removed.');
    }

    // -------------------------------------------------------
    // Service Category CRUD
    // -------------------------------------------------------
    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:service_categories,name',
        ]);
        ServiceCategory::create($data);
        return redirect('/admin/services')->with('success', 'Category added.');
    }

    public function destroyCategory(ServiceCategory $category)
    {
        $category->delete();
        return redirect('/admin/services')->with('success', 'Category removed.');
    }

    public function edit(Service $service)
    {
        // Editing is done inline on the index page via ?edit=id
        return redirect('/admin/services?edit=' . $service->id);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'nullable|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'car_type_id' => 'nullable|exists:car_types,id',
            'car_model_id' => 'nullable|exists:car_models,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($service->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($validated);
        return redirect('/admin/services')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        // Block deletion if there are any active (non-completed, non-cancelled) bookings
        $activeBookings = $service->bookings()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($activeBookings) {
            return back()->with('error', 'A booked service cannot be deleted.');
        }

        // Safe to delete — all bookings are completed or cancelled (or none at all)
        $service->delete();
        return back()->with('success', 'Service deleted successfully.');
    }

    // -------------------------------------------------------
    // Public page — services list (browse without provider context)
    // -------------------------------------------------------
    public function publicServices(Request $request)
    {
        $selectedCar = session('selected_car_model');
        $query = Service::query();

        if ($selectedCar) {
            $carModel = CarModel::find($selectedCar['id']);
            if ($carModel) {
                $query->where(function ($q) use ($carModel) {
                    $q->whereNull('car_type_id')->orWhere('car_type_id', $carModel->car_type_id);
                })->where(function ($q) use ($carModel) {
                    $q->whereNull('car_model_id')->orWhere('car_model_id', $carModel->id);
                });
            }
        }

        $services = $query->orderBy('name')->get();
        $allCarTypes = CarType::with('models')->get();

        return view('services.index', compact('services', 'allCarTypes', 'selectedCar'));
    }

    // -------------------------------------------------------
    // Car selection (kept from original)
    // -------------------------------------------------------
    public function selectCar(Request $request)
    {
        $request->validate(['car_model_id' => 'required|exists:car_models,id']);

        $carModel = CarModel::with('carType')->find($request->car_model_id);
        session([
            'selected_car_model' => [
                'id' => $carModel->id,
                'name' => $carModel->name,
                'type_name' => optional($carModel->carType)->name ?? 'Car',
                'price_modifier' => $carModel->price_modifier,
            ]
        ]);

        if (auth()->check()) {
            auth()->user()->update(['car_model_id' => $carModel->id]);
        }

        $cookie = cookie()->forever('selected_car_model_id', $carModel->id);

        return redirect()->back()->with('success', 'Car selected: ' . $carModel->name)->withCookie($cookie);
    }

    // -------------------------------------------------------
    // API helpers (kept for price calculation)
    // -------------------------------------------------------
    public function getCarModels(Request $request)
    {
        if ($request->has('car_type_id')) {
            return CarModel::where('car_type_id', $request->car_type_id)->get();
        }
        return CarModel::all();
    }

    public function calculatePrice(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'car_model_id' => 'required|exists:car_models,id',
        ]);

        $service = Service::find($request->service_id);
        $carModel = CarModel::find($request->car_model_id);
        $finalPrice = round($service->base_price * $carModel->price_modifier, 2);

        return response()->json([
            'base_price' => $service->base_price,
            'modifier' => $carModel->price_modifier,
            'final_price' => $finalPrice,
        ]);
    }

    // Backward compat alias
    public function publicIndex(Request $request)
    {
        return $this->index();
    }
}
