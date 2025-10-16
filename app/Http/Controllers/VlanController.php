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
        // ambil VLAN
        $vlan = VlanModel::findOrFail($id);

        // ambil domain terkait (untuk ditampilkan sebagai judul)
        $domain = DomainModel::find($vlan->domain);

        // dasar query IP untuk vlan ini
        $search = trim($request->input('search', ''));
        $dataQuery = \DB::table('tb_ip as ip')
            ->leftJoin('tb_vlan as vlan', 'ip.vlan', '=', 'vlan.id')
            ->leftJoin('tb_service as s', 'ip.service', '=', 's.id')
            ->where('ip.isdeleted', 0)
            ->where('ip.vlan', $id);

        if ($search) {
            $like = '%' . strtolower($search) . '%';
            $dataQuery->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(ip.ip) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.device) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(s.service, ip.service)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.rack) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.location) LIKE ?', [$like]);
            });
        }

        $data = $dataQuery->select(
            'ip.*',
            'vlan.vlan as vlan_name',
            'vlan.block_ip',
            'vlan.gateway',
            \DB::raw('COALESCE(s.service, ip.service) as service_name'),
            \DB::raw('COALESCE(s.customer, "") as customer'),
            \DB::raw('COALESCE(s.longlat, "") as longlat'),
            \DB::raw('COALESCE(s.location, "") as service_location')
        )
            ->orderByRaw('INET_ATON(ip.ip) asc')
            ->paginate(50)
            ->appends(['search' => $search]);

        // jadikan judul nama domain jika tersedia
        $title = ($domain && !empty($domain->domain)) ? $domain->domain : 'Detail VLAN';

        return view('domain.detail', compact('data', 'vlan', 'title', 'domain'));
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
