<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceModel;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // search across id, service, description, customer, location, longlat
        $query = ServiceModel::where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('service', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('longlat', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('id', 'asc')->paginate(5)->appends(['search' => $search]);
        $title = 'Service';
        return view('service.service', compact('services', 'title', 'search'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'service' => 'required',
            'description' => 'required',
            // 'customer' => 'required',
            // 'location' => 'required',
            // 'longlat' => 'required',
        ]);

        ServiceModel::create([
            'service' => $request->service,
            'description' => $request->description,
            'customer' => $request->customer,
            'location' => $request->location,
            'longlat' => $request->longlat,


            // 'iby' => auth()->user()->name,
            'idt' => now(),
        ]);

        return redirect()->route('service.service')->with('success', 'Service created successfully.');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'service' => 'required',
            'description' => 'required',
            // 'customer' => 'required',
            // 'location' => 'required',
            // 'longlat' => 'required',
        ]);

        $service = ServiceModel::findOrFail($id);
        $service->update([
            'service' => $request->service,
            'description' => $request->description,
            'customer' => $request->customer,
            'location' => $request->location,
            'longlat' => $request->longlat,

            // 'uby' => auth()()->name,
            'udt' => now(),
        ]);

        return redirect()->route('service.service')->with('success', 'Service updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $service = ServiceModel::findOrFail($id);
        $service->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);
        return redirect()->route('service.service')->with('success', 'Service deleted successfully.');
    }
}
