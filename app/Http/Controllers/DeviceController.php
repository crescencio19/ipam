<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceModel;
use App\Models\DomainModel;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // eager load domain relation and search device / description / domain name (OR logic)
        $query = DeviceModel::with('domainData')->where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('device', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('domainData', function ($q2) use ($search) {
                        $q2->where('domain', 'like', "%{$search}%")->where('isdeleted', 0);
                    });
            });
        }

        $devices = $query->orderBy('device', 'asc')->paginate(5)->appends(['search' => $search]);
        $domains = DomainModel::where('isdeleted', 0)->get();
        $title = 'Devices';
        return view('device.device', compact('devices', 'domains', 'title'));
    }
    public function create()
    {
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('device.create', compact('domains'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'device' => 'required',
            'description' => 'required',
            'domain' => 'required',
        ]);
        DeviceModel::create($request->all());
        return redirect()->route('device.device')->with('success', 'Device created successfully.');
    }
    public function edit($id)
    {
        $device = DeviceModel::findOrFail($id);
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('devices.edit', compact('device', 'domains'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'device' => 'required',
            'description' => 'required',
            'domain' => 'required',
        ]);
        $device = DeviceModel::findOrFail($id);
        $device->update($request->all());
        return redirect()->route('device.device')->with('success', 'Device updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $device = DeviceModel::findOrFail($id);
        $device->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);
        return redirect()->route('device.device')->with('success', 'Device deleted successfully.');
    }
    public function byDomain(Request $request)
    {
        $domain = $request->input('domain');
        if (!$domain) {
            return response()->json([]);
        }

        // ambil kolom `device` dari tb_device, gunakan device untuk devicecs juga
        $list = DeviceModel::where('isdeleted', 0)
            ->where('domain', $domain)
            ->orderBy('device', 'asc')
            ->get(['device']);

        return response()->json($list);
    }
}
