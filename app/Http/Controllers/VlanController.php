<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VlanModel;
use App\Models\DomainModel;
use Illuminate\Support\Facades\DB;

class VlanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // search across id, vlanid, vlan, gateway, block_ip and related domain name
        $query = VlanModel::where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('vlanid', 'like', "%{$search}%")
                    ->orWhere('vlan', 'like', "%{$search}%")
                    ->orWhere('gateway', 'like', "%{$search}%")
                    ->orWhere('block_ip', 'like', "%{$search}%")
                    ->orWhereHas('domainData', function ($q2) use ($search) {
                        $q2->where('domain', 'like', "%{$search}%")
                            ->where('isdeleted', 0);
                    });
            });
        }

        $vlans = $query->orderBy('id', 'asc')->paginate(5)->appends(['search' => $search]);
        $domains = DomainModel::where('isdeleted', 0)->get();
        $title = 'Vlan';
        return view('vlan.vlan', compact('vlans', 'title', 'domains'));
    }

    public function create()
    {
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('vlan.create', compact('domains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required',
            'vlan' => 'required',
            'gateway' => 'required',
            'block_ip' => 'required',
        ]);

        VlanModel::create([
            'domain' => $request->domain,
            'vlan' => $request->vlan,
            'vlanid' => $request->vlanid,
            'gateway' => $request->gateway,
            'block_ip' => $request->block_ip,
        ]);

        return redirect()->route('vlan.vlan')->with('success', 'VLAN created successfully.');
    }
    public function edit($id)
    {
        $vlan = VlanModel::findOrFail($id);
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('vlan.edit', compact('vlan', 'domains'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'domain' => 'required',
            'vlan' => 'required',
            'vlanid' => 'required',
            'gateway' => 'required',
            'block_ip' => 'required',
        ]);

        $vlan = VlanModel::findOrFail($id);
        $vlan->update([
            'domain' => $request->domain,
            'vlan' => $request->vlan,
            'vlanid' => $request->vlanid,
            'gateway' => $request->gateway,
            'block_ip' => $request->block_ip,
        ]);

        return redirect()->route('vlan.vlan')->with('success', 'VLAN updated successfully.');
    }


    public function destroy(Request $request, $id)
    {
        $vlan = VlanModel::findOrFail($id);
        $vlan->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);
        return redirect()->route('vlan.vlan')->with('success', 'Vlan deleted successfully.');
    }
    public function show($id, Request $request)
    {
        $vlan = VlanModel::with('domainData')->findOrFail($id);
        // ambil ip terkait (sesuaikan query yg Anda inginkan)
        $ips = DB::table('tb_ip')->where('vlan', $id)->where('isdeleted', 0)
            ->orderByRaw('INET_ATON(ip) asc') // butuh MySQL INET_ATON; ubah jika tidak tersedia
            ->paginate(50);

        return view('vlan.detailvlan', ['vlan' => $vlan, 'data' => $ips, 'title' => 'VLAN Detail']);
    }

    public function dashboard(Request $request)
    {
        $search = $request->input('search');
        $query = VlanModel::with('domainData')->where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vlanid', 'like', "%{$search}%")
                    ->orWhere('vlan', 'like', "%{$search}%");
            })->orWhereHas('domainData', function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")->where('isdeleted', 0);
            });
        }

        $vlans = $query->orderBy('vlanid')->paginate(20)->appends(['search' => $search]);
        $title = ' Vlan Ranges';
        return view('dasvlan', compact('vlans', 'title', 'search'));
    }
}
