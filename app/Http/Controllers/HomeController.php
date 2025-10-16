<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DomainModel;
use Illuminate\Support\Facades\DB;
use App\Models\IpModel;
use App\Models\VlanModel;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // jika ada pencarian, coba cari IP/device/vlan/service yang cocok
        if ($search) {
            $match = DB::table('tb_ip as ip')
                ->join('tb_vlan as vlan', 'ip.vlan', '=', 'vlan.id')
                ->leftJoin('tb_service as service', 'ip.service', '=', 'service.id')
                ->where('ip.isdeleted', 0)
                ->where('vlan.isdeleted', 0)
                ->where(function ($q) use ($search) {
                    $q->where('ip.ip', 'like', "%{$search}%")
                        ->orWhere('ip.device', 'like', "%{$search}%")
                        ->orWhere('vlan.vlan', 'like', "%{$search}%")
                        ->orWhere('service.service', 'like', "%{$search}%");
                        
                })
                ->select('vlan.domain as domain_id')
                ->first();

            if ($match && $match->domain_id) {
                // redirect ke halaman detail domain dengan query search
                return redirect()->route('domain.show', ['id' => $match->domain_id, 'search' => $search]);
            }

            // jika tidak ada match IP, filter domains berdasarkan nama domain agar view tahu hasilnya kosong
            $domains = DomainModel::where('isdeleted', 0)
                ->where('domain', 'like', "%{$search}%")
                ->get();
        } else {
            $domains = DomainModel::where('isdeleted', 0)->get();
        }

        foreach ($domains as $domain) {
            $domain->vlan_count = VlanModel::where('domain', $domain->id)->where('isdeleted', 0)->count();
            $domain->ip_count = IpModel::whereHas('VlanData', function ($q) use ($domain) {
                $q->where('domain', $domain->id);
            })->where('isdeleted', 0)->count();
        }
        return view('index', compact('domains'));
    }
    public function show($id, Request $request)
    {
        $domain = DomainModel::findOrFail($id);
        $title = 'Detail IP';
        $search = $request->input('search');

        // tentukan apakah domain adalah "intra"
        $isIntra = (isset($domain->type) && strtolower($domain->type) === 'intra')
            || (stripos($domain->domain ?? '', 'intra') !== false);

        // setelah ambil $domain
        $isEnterprise = stripos($domain->domain ?? '', 'enterprise') !== false;

        $base = DB::table('tb_ip as ip')
            ->join('tb_vlan as vlan', 'ip.vlan', '=', 'vlan.id')
            ->join('tb_domain as domain', 'vlan.domain', '=', 'domain.id')
            ->leftJoin('tb_service as service', function ($join) {
                $join->on('ip.service', '=', 'service.id')
                    ->orOn('ip.service', '=', 'service.service');
            })
            ->where('ip.isdeleted', 0)
            ->where('vlan.isdeleted', 0)
            ->where('domain.isdeleted', 0)
            ->where('domain.id', $domain->id);

        if (!$isEnterprise) {
            // non-enterprise: tampilkan rack + location dari ip
            $base = $base->select(
                'ip.id as no',
                'ip.device as device',
                'ip.ip as ip',
                'ip.vlanid as vlanid',
                'vlan.vlan as vlan_name',
                'service.service as service_name',
                'vlan.block_ip as block_ip',
                'vlan.gateway as gateway',
                'ip.rack as rack',
                'ip.location as location'
            );
        } else {
            // enterprise: tampilkan customer/longlat dari service (tidak menampilkan rack)
            $base = $base->select(
                'ip.id as no',
                'ip.device as device',
                'ip.ip as ip',
                'ip.vlanid as vlanid',
                'vlan.vlan as vlan_name',
                'service.service as service_name',
                'vlan.block_ip as block_ip',
                'vlan.gateway as gateway',
                'service.customer as customer',
                'ip.bandwith as bandwith',
                'service.longlat as longlat',
                'service.location as service_location'
            );
        }

        if ($search) {
            $base = $base->where(function ($q) use ($search) {
                $q->where('ip.ip', 'like', "%{$search}%")
                    ->orWhere('ip.device', 'like', "%{$search}%")
                    ->orWhere('vlan.vlan', 'like', "%{$search}%")
                    ->orWhere('service.service', 'like', "%{$search}%");
            });
        }

        $data = $base->orderByRaw('INET_ATON(ip.ip) asc')->paginate(50)->appends(['search' => $search]);

        // kirim isEnterprise ke view agar view tahu branch mana yang dipakai
        return view('domain.detail', compact('data', 'domain', 'title', 'isEnterprise'));
    }
}
