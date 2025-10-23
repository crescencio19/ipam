<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\IpModel;
use App\Models\ServiceModel;
use App\Models\VlanModel;

use Illuminate\Support\Facades\DB;

class IpController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = DB::table('tb_ip as ip')
            ->select(
                'ip.*',
                'v.vlan as vlan_name',
                DB::raw("COALESCE(srv2.service, srv1.service, s.service, ip.service) as service_name"),
                'v.vlanid as vlanid_value'
            )
            ->leftJoin('tb_vlan as v', 'ip.vlan', '=', 'v.id')
            // jika ip.service = tb_service.id
            ->leftJoin('tb_service as s', 'ip.service', '=', 's.id')
            // jika tb_service.service = master.service.id
            ->leftJoin('service as srv1', 's.service', '=', 'srv1.id')
            // jika ip.service = master.service.id langsung
            ->leftJoin('service as srv2', 'ip.service', '=', 'srv2.id')
            ->where('ip.isdeleted', 0);

        if ($search) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('ip.ip', 'like', $like)
                    ->orWhere('ip.device', 'like', $like)
                    ->orWhere('ip.devicecs', 'like', $like)
                    ->orWhere('ip.ipcs', 'like', $like)
                    ->orWhere('ip.vlanid', 'like', $like)
                    ->orWhere('v.vlan', 'like', $like)
                    ->orWhere('ip.rack', 'like', $like)
                    ->orWhere('ip.bandwith', 'like', $like)
                    ->orWhere('ip.location', 'like', $like)
                    ->orWhere('ip.r_number', 'like', $like)
                    ->orWhere('ip.b_number', 'like', $like)
                    // cek semua sumber nama service (tb_service, master service, atau ip.service langsung)
                    ->orWhere('s.service', 'like', $like)
                    ->orWhere('srv1.service', 'like', $like)
                    ->orWhere('srv2.service', 'like', $like)
                    // fallback: COALESCE dari select alias
                    ->orWhereRaw("COALESCE(srv2.service, srv1.service, s.service, ip.service) LIKE ?", [$like]);
            });
        }

        $ips = $query->orderBy('ip.id', 'desc')->paginate(5)->appends(['search' => $search]);

        $title = 'IP Services';
        $vlans = VlanModel::where('isdeleted', 0)->get();
        $services = ServiceModel::where('isdeleted', 0)->get();

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
        // basic validation (don't require vlanid from client)
        $request->validate([
            'vlan' => 'required',
            'ip' => 'required',
            'device' => 'required',

        ]);


        $request->validate([
            'ip' => [
                'required',
                'max:45',
                Rule::unique('tb_ip', 'ip')->where(function ($query) {
                    $query->where('isdeleted', 0);
                }),
            ],
            'ipcs' => [
                'nullable',
                'max:100',
                Rule::unique('tb_ip', 'ipcs')->where(function ($query) {
                    $query->where('isdeleted', 0);
                }),
            ],
            // ...add other rules as needed...
        ]);

        IpModel::create([
            'vlan' => $request->input('vlan'),
            'vlanid' => $request->input('vlanid'),
            'device' => $request->input('device'),
            'devicecs' => $request->input('devicecs'),
            'ipcs' => $request->input('ipcs'),
            'r_number' => $request->input('r_number'),
            'b_number' => $request->input('b_number'),
            'ip' => $request->input('ip'),
            'service' => $request->input('service'),
            'rack' => $request->input('rack'),
            'bandwith' => $request->input('bandwith'),
            'location' => $request->input('location'),

        ]);


        return redirect()->route('ip.ip')->with('success', 'IP created.');
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
        // validasi: service boleh null (sama seperti create)
        $request->validate([
            'vlan' => 'required',
            'ip' => 'required',
            'device' => 'required',
            'service' => 'nullable',
        ]);

        $request->validate([
            'ip' => [
                'required',
                'max:45',
                Rule::unique('tb_ip', 'ip')->ignore($id, 'id')->where(function ($query) {
                    $query->where('isdeleted', 0);
                }),
            ],
            'ipcs' => [
                'nullable',
                'max:100',
                Rule::unique('tb_ip', 'ipcs')->ignore($id, 'id')->where(function ($query) {
                    $query->where('isdeleted', 0);
                }),
            ],
            // ...add other rules as needed...
        ]);

        Log::info('IpController::update payload', array_merge(['id' => $id], $request->all()));

        // resolve vlanid dari pilihan vlan jika client tidak mengirim vlanid
        $vlanid = $request->input('vlanid');
        if (empty($vlanid) && $request->filled('vlan')) {
            $v = VlanModel::find($request->vlan);
            if ($v) $vlanid = $v->vlanid;
        }

        $ip = IpModel::findOrFail($id);
        $update = $request->only([
            'vlan',
            'device',
            'devicecs',
            'ipcs',
            'r_number',
            'b_number',
            'ip',
            'service',
            'rack',
            'bandwith',
            'location',
            'remark',
            'uby',
            'udt',
            'dby',
            'ddt'
        ]);
        $update['vlanid'] = $vlanid;

        $ip->update($update);

        return redirect()->route('ip.ip')->with('success', 'IP updated.');
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
