<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Worker;
use App\Models\User;
use App\Models\ServiceProvider;

class WorkerController extends Controller
{
    private function getProvider(): ServiceProvider
    {
        $sp = Auth::user()->serviceProvider;
        abort_unless($sp, 403, 'Not a service provider account.');
        return $sp;
    }

    public function index()
    {
        $provider = $this->getProvider();
        $workers  = $provider->workers()->latest()->get();
        return view('provider.workers.index', compact('provider', 'workers'));
    }

    public function create()
    {
        $provider = $this->getProvider();
        return view('provider.workers.create', compact('provider'));
    }

    public function store(Request $request)
    {
        $provider = $this->getProvider();

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'cnic'             => 'required|string|max:15|unique:workers,cnic',
            'address'          => 'nullable|string|max:500',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'required|email|max:255|unique:users,email',
            'experience_years' => 'nullable|integer|min:0',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        // 1. Create a User record so the worker can log in via the standard auth
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => 'worker',
            'phone_number' => $data['phone'] ?? null,
            'address'  => $data['address'] ?? null,
            'cnic'     => $data['cnic'],
        ]);

        // 2. Create the worker row linked to the user
        $provider->workers()->create([
            'user_id'          => $user->id,
            'name'             => $data['name'],
            'cnic'             => $data['cnic'],
            'address'          => $data['address'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'email'            => $data['email'],
            'experience_years' => $data['experience_years'] ?? 0,
            'password'         => bcrypt($data['password']),
            'is_available'     => true,
        ]);

        return redirect()->route('provider.workers.index')->with('success', 'Worker added successfully. They can now log in with their email and password.');
    }

    public function edit(Worker $worker)
    {
        $provider = $this->getProvider();
        abort_unless($worker->service_provider_id === $provider->id, 403);
        return view('provider.workers.edit', compact('provider', 'worker'));
    }

    public function update(Request $request, Worker $worker)
    {
        $provider = $this->getProvider();
        abort_unless($worker->service_provider_id === $provider->id, 403);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'cnic'             => 'required|string|max:15|unique:workers,cnic,' . $worker->id,
            'address'          => 'nullable|string|max:500',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'required|email|max:255|unique:users,email,' . ($worker->user_id ?? 'NULL'),
            'experience_years' => 'nullable|integer|min:0',
            'password'         => 'nullable|string|min:6|confirmed',
        ]);

        // Update worker row
        $workerUpdate = [
            'name'             => $data['name'],
            'cnic'             => $data['cnic'],
            'address'          => $data['address'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'email'            => $data['email'],
            'experience_years' => $data['experience_years'] ?? 0,
        ];

        if (!empty($data['password'])) {
            $workerUpdate['password'] = bcrypt($data['password']);
        }

        $worker->update($workerUpdate);

        // Sync the linked User record
        if ($worker->user_id) {
            $userUpdate = [
                'name'         => $data['name'],
                'email'        => $data['email'],
                'phone_number' => $data['phone'] ?? null,
                'address'      => $data['address'] ?? null,
                'cnic'         => $data['cnic'],
            ];
            if (!empty($data['password'])) {
                $userUpdate['password'] = bcrypt($data['password']);
            }
            User::where('id', $worker->user_id)->update($userUpdate);
        }

        return redirect()->route('provider.workers.index')->with('success', 'Worker updated.');
    }

    public function destroy(Worker $worker)
    {
        $provider = $this->getProvider();
        abort_unless($worker->service_provider_id === $provider->id, 403);

        // Delete the linked user account so they can no longer log in
        if ($worker->user_id) {
            User::where('id', $worker->user_id)->delete();
        }

        $worker->delete();
        return redirect()->route('provider.workers.index')->with('success', 'Worker removed.');
    }

    // Keep for API compatibility
    public function getAvailableWorkers(Request $request)
    {
        $provider = Auth::user()->serviceProvider;
        if (!$provider) return response()->json([]);
        return response()->json($provider->workers()->where('is_available', true)->get());
    }
}
