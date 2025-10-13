<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\DomainModel;
use Illuminate\Support\Facades\DB;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = DomainModel::where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $domains = $query->orderBy('id', 'asc')->paginate(5)->appends(['search' => $search]);
        $title = 'Domain';
        return view('domain.domain', compact('domains', 'title', 'search'));
    }

    public function create()
    {
        return view('domain.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        DomainModel::create([
            'domain' => $request->domain,
            'description' => $request->description,
            'idt' => now(),
        ]);

        return redirect()->route('domain.domain')->with('success', 'Domain created successfully.');
    }
    public function edit($id)
    {
        $domains = DomainModel::findOrFail($id);
        return view('domain.edit', compact('domains'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $domain = DomainModel::findOrFail($id);
        $domain->update([
            'domain' => $request->domain,
            'description' => $request->description,
            'udt' => now(),
        ]);

        return redirect()->route('domain.domain')->with('success', 'Domain updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $domain = DomainModel::findOrFail($id);
        $domain->update([
            'isdeleted' => 1,
            'remark' => $request->remark,
            'ddt' => now(),
        ]);

        return redirect()->route('domain.domain')->with('success', 'Domain deleted successfully.');
    }
    public function getIpsByDomain($id)
    {
        $ips = DB::table('tb_ip as ip')
            ->leftJoin('tb_service as service', function ($join) {
                $join->on('ip.service', '=', 'service.id')
                    ->orOn('ip.service', '=', 'service.service');
            })
            ->leftJoin('tb_vlan as vlan', 'ip.vlan', '=', 'vlan.id')
            ->where('ip.isdeleted', 0)
            ->where('vlan.domain', $id)
            ->select(
                'ip.*',
                'ip.id',
                'ip.device',
                'ip.ip',
                'ip.vlanid',
                'ip.bandwith as bandwith',          // bandwidth in ip table (adjust name if different)
                'vlan.vlan as vlan_name',
                'vlan.block_ip',
                'vlan.gateway',
                'service.service as service_name',
                'service.customer as customer',     // from tb_service
                'service.longlat as longlat',       // from tb_service
                'ip.rack',
                'ip.location'
            )
            ->orderByRaw('INET_ATON(ip.ip) asc')
            ->paginate(50);

        return response()->json($ips);
    }
    public function show($id, Request $request)
    {
        $search = $request->input('search');

        $dataQuery = \DB::table('tb_ip as ip')
            ->leftJoin('tb_vlan as vlan', 'ip.vlan', '=', 'vlan.id')
            ->leftJoin('tb_service as service', function ($join) {
                // ip.service bisa berisi id atau nama service -> handle keduanya
                $join->on('ip.service', '=', 'service.id')
                    ->orOn('ip.service', '=', 'service.service');
            })
            ->where('ip.isdeleted', 0)
            ->where('vlan.domain', $id);

        if ($search) {
            $dataQuery->where(function ($q) use ($search) {
                $q->where('ip.ip', 'like', "%{$search}%")
                    ->orWhere('ip.device', 'like', "%{$search}%")
                    ->orWhere('service.service', 'like', "%{$search}%");
            });
        }

        $data = $dataQuery->select(
            'ip.*',
            'ip.bandwith as bandwith',
            'vlan.vlan as vlan_name',
            'vlan.block_ip',
            'vlan.gateway',
            'service.service as service_name',
            'service.customer as customer',
            'service.longlat as longlat',
            'service.location as service_location', // <-- dari tb_service
            'ip.rack',
            'ip.location as ip_location'
        )
            ->orderByRaw('INET_ATON(ip.ip) asc')
            ->paginate(50)
            ->appends(['search' => $search]);

        $domain = \App\Models\DomainModel::find($id);
        $title = 'Detail IP';

        return view('domain.detail', compact('data', 'domain', 'title'));
    }
}
