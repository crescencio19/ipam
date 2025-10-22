<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ServiceModel;
use App\Models\DomainModel;
use App\Models\ServicesModel;

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

        $service = ServicesModel::where('isdeleted', 0)->get();
        $domains = DomainModel::where('isdeleted', 0)->get();
        $services = $query->orderBy('id', 'asc')->paginate(5)->appends(['search' => $search]);
        $title = 'Service';
        return view('service.service', compact('services', 'title', 'search', 'domains', 'service'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            // use Rule::exists with DomainModel table name to avoid assuming 'domains' table
            'domain' => ['required', Rule::exists((new DomainModel)->getTable(), 'id')],
            'service' => ['required', Rule::exists((new ServicesModel)->getTable(), 'id')],
            'customer' => 'nullable|string',
            'location' => 'nullable|string',
            'longlat' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // simpan menggunakan ServiceModel (sama table yang dipakai di listing)
        ServiceModel::create([
            'domain' => $data['domain'],
            'service' => $data['service'], // sekarang menyimpan service_id
            'customer' => $data['customer'] ?? null,
            'location' => $data['location'] ?? null,
            'longlat' => $data['longlat'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('service.service')->with('success', 'Saved');
    }


    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'domain' => ['required', Rule::exists((new DomainModel)->getTable(), 'id')],
            'service' => ['required', Rule::exists((new ServicesModel)->getTable(), 'id')],
            'description' => 'required',
            'customer' => 'nullable|string',
            'location' => 'nullable|string',
            'longlat' => 'nullable|string',
        ]);

        $service = ServiceModel::findOrFail($id);
        $service->update([
            'domain' => $data['domain'],
            'service' => $data['service'],
            'description' => $data['description'],
            'customer' => $data['customer'] ?? null,
            'location' => $data['location'] ?? null,
            'longlat' => $data['longlat'] ?? null,
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

    public function byDomain(Request $request)
    {
        $domainId = $request->input('domain');
        if (!$domainId) return response()->json([]);

        $list = ServicesModel::where('isdeleted', 0)
            ->where('domain', $domainId)
            ->orderBy('service', 'asc')
            ->get(['id', 'service']);

        return response()->json($list);
    }
}
