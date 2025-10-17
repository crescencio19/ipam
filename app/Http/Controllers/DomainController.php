<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\DomainModel;
use App\Models\IpModel;
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
            // join service by id and by name separately for reliability
            ->leftJoin('tb_service as s_id', 'ip.service', '=', 's_id.id')
            ->leftJoin('tb_service as s_name', 'ip.service', '=', 's_name.service')
            ->where('ip.isdeleted', 0)
            ->where('vlan.domain', $id);

        if ($search) {
            $s = strtolower(trim($search));
            $like = "%{$s}%";
            $dataQuery->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(ip.ip) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.device) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(vlan.vlan) LIKE ?', [$like])
                    // search service from either join (coalesce)
                    ->orWhereRaw('LOWER(COALESCE(s_id.service, s_name.service, ip.service)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(vlan.gateway) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.rack) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.location) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(s_id.customer, s_name.customer)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip.bandwith) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(s_id.longlat, s_name.longlat)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(s_id.location, s_name.location)) LIKE ?', [$like]);
            });
        }

        $data = $dataQuery->select(
            'ip.*',
            'ip.bandwith as bandwith',
            'vlan.vlan as vlan_name',
            'vlan.block_ip',
            'vlan.gateway',
            // prefer s_id then s_name, fallback ip.service text
            \DB::raw('COALESCE(s_id.service, s_name.service, ip.service) as service_name'),
            \DB::raw('COALESCE(s_id.customer, s_name.customer) as customer'),
            \DB::raw('COALESCE(s_id.longlat, s_name.longlat) as longlat'),
            \DB::raw('COALESCE(s_id.location, s_name.location) as service_location'),
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
    public function detail(Request $request, $id)
    {
        $term = trim($request->get('search', ''));

        $query = IpModel::query()
            ->where('domain_id', $id); // contoh filter domain

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('device', 'like', "%{$term}%")
                    ->orWhere('ip', 'like', "%{$term}%")
                    ->orWhere('vlanid', 'like', "%{$term}%")
                    ->orWhere('vlan_name', 'like', "%{$term}%")
                    // tambahkan kolom block_ip, rack, location
                    ->orWhere('block_ip', 'like', "%{$term}%")
                    ->orWhere('rack', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
                // jika service adalah relasi, bisa gunakan orWhereHas:
                // ->orWhereHas('serviceRelation', fn($s) => $s->where('service','like', "%{$term}%"));
            });
        }

        $data = $query->orderBy('ip')->paginate(50);

        return view('domain.detail', compact('data', 'title', 'domain', 'vlan'));
    }
}
