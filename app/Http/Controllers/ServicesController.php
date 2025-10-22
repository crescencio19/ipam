<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicesModel;
use App\Models\DomainModel;


class ServicesController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->input('search');

        // search across id, service, description and related domain name
        $query = ServicesModel::where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('service', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('domainData', function ($q2) use ($search) {
                        $q2->where('domain', 'like', "%{$search}%")
                            ->where('isdeleted', 0);
                    });
            });
        }

        $services = $query->orderBy('id', 'asc')->paginate(5)->appends(['search' => $search]);
        $domains = DomainModel::where('isdeleted', 0)->get();
        $title = 'Services';
        return view('services.services', compact('services', 'title', 'domains'));
    }
    public function create()
    {
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('services.create', compact('domains'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required',
            'service' => 'required',
            'description' => 'required',
        ]);

        ServicesModel::create([
            'domain' => $request->domain,
            'service' => $request->service,
            'description' => $request->description,
        ]);

        return redirect()->route('services.services')->with('success', 'Service created successfully.');
    }

    public function edit($id)
    {
        $service = ServicesModel::findOrFail($id);
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('services.edit', compact('service', 'domains'));
    }   

    public function update(Request $request, $id)
    {
        $request->validate([
            'domain' => 'required',
            'service' => 'required',
            'description' => 'required',
        ]);

        $service = ServicesModel::findOrFail($id);
        $service->update([
            'domain' => $request->domain,
            'service' => $request->service,
            'description' => $request->description,
        ]);

        return redirect()->route('services.services')->with('success', 'Service updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $service = ServicesModel::findOrFail($id);
        $service->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);
        return redirect()->route('services.services')->with('success', 'Service deleted successfully.');
    }
}
