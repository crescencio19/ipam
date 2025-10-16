<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\IpModel;
use App\Models\ServiceModel;
use App\Models\VlanModel;

class IpController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = \DB::table('tb_ip as ip')
            ->select('ip.*', 'v.vlan as vlan_name', 's.service as service_name', 'v.vlanid as vlanid_value') // tambahkan vlanid_value
            ->leftJoin('tb_vlan as v', 'ip.vlan', '=', 'v.id')
            ->leftJoin('tb_service as s', 'ip.service', '=', 's.id')
            ->where('ip.isdeleted', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ip.ip', 'like', "%{$search}%")
                    ->orWhere('ip.device', 'like', "%{$search}%")
                    ->orWhere('v.vlan', 'like', "%{$search}%")
                    ->orWhere('s.service', 'like', "%{$search}%");
            });
        }

        $ips = $query->orderBy('ip.id', 'desc')->paginate(5)->appends(['search' => $search]);

        $title = 'IP Services';
        $vlans = \App\Models\VlanModel::where('isdeleted', 0)->get();
        $services = \App\Models\ServiceModel::where('isdeleted', 0)->get();

        return view('ip.ip', compact('ips', 'title', 'vlans', 'services', 'search'));
    }

    public function create()
    {
        $vlans = VlanModel::where('isdeleted', 0)->get();
        $services = ServiceModel::where('isdeleted', 0)->get();
        return view('ip.create', compact('vlans', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vlan' => 'required',
            'ip' => 'required',
            'device' => 'required',
            'vlanid' => 'required',
            'service' => 'nullable|exists:tb_service,id', // <- tambahkan
        ]);

        $payload = [
            'vlan' => $request->vlan,
            'ip' => $request->ip,
            'device' => $request->device,
            'vlanid' => $request->vlanid,
            'rack' => $request->rack,
            'bandwith' => $request->bandwith,
            'location' => $request->location,
            'idt' => now(),
        ];

        // hanya set service jika tidak kosong
        if ($request->filled('service') && $request->service !== '') {
            $payload['service'] = $request->service;
        }

        IpModel::create($payload);

        return redirect()->route('ip.ip')->with('success', 'IP created successfully.');
    }

    public function edit($id)
    {
        $ip = IpModel::findOrFail($id);
        $vlans = VlanModel::where('isdeleted', 0)->get();
        $services = ServiceModel::where('isdeleted', 0)->get();
        return view('ip.edit', compact('ip', 'vlans', 'services'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vlan' => 'required',
            'ip' => 'required',
            'device' => 'required',
            'vlanid' => 'required',
            'service' => 'nullable|exists:tb_service,id',
        ]);

        $ip = IpModel::findOrFail($id);

        $update = [
            'vlan' => $request->vlan,
            'ip' => $request->ip,
            'device' => $request->device,
            'vlanid' => $request->vlanid,
            'rack' => $request->rack,
            'bandwith' => $request->bandwith,
            'location' => $request->location,
        ];

        // Jika request mengirim service (termasuk empty string), set sesuai:
        if ($request->has('service')) {
            // jika kosong => set null (membersihkan), kalau ada id => set id
            $update['service'] = trim((string)$request->service) === '' ? null : $request->service;
        }

        $ip->update($update);

        return redirect()->route('ip.ip')->with('success', 'IP updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $ip = IpModel::findOrFail($id);
        $ip->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);
        return redirect()->route('ip.ip')->with('success', 'IP deleted successfully.');
    }
}
