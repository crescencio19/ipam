<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DomainModel;
use Illuminate\Support\Facades\DB;
use App\Models\IpModel;
use App\Models\VlanModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            // jika ip.service menunjuk tb_service (bisa berupa id atau berupa service string)
            ->leftJoin('tb_service as s', function ($join) {
                $join->on('ip.service', '=', 's.id')
                    ->orOn('ip.service', '=', 's.service');
            })
            // jika tb_service.service = master.service.id
            ->leftJoin('service as srv1', 's.service', '=', 'srv1.id')
            // jika ip.service = master.service.id langsung
            ->leftJoin('service as srv2', 'ip.service', '=', 'srv2.id')
            ->where('ip.isdeleted', 0)
            ->where('vlan.isdeleted', 0)
            ->where('domain.isdeleted', 0)
            ->where('domain.id', $domain->id);

        // pilih semua kolom yang diperlukan view detail (kosong jika tidak ada)
        $base = $base->select(
            'ip.id as no',
            'ip.device as device',
            'ip.devicecs as devicecs',
            'ip.ip as ip',
            'ip.ipcs as ipcs',
            'ip.vlanid as vlanid',
            'vlan.vlan as vlan_name',
            DB::raw("COALESCE(srv2.service, srv1.service, s.service, ip.service) as service_name"),
            // ambil customer/location/longlat dari tb_service (s) — master service tidak menyimpan field ini
            's.customer as customer',
            'vlan.block_ip as block_ip',
            'vlan.gateway as gateway',
            's.location as service_location',
            's.longlat as longlat',
            'ip.rack as rack',
            'ip.bandwith as bandwith',
            'ip.location as location',
            'ip.r_number as r_number',
            'ip.b_number as b_number'
        );

        if ($search) {
            $like = "%{$search}%";
            $base = $base->where(function ($q) use ($search, $like) {
                $q->where('ip.ip', 'like', $like)
                    ->orWhere('ip.device', 'like', $like)
                    ->orWhere('vlan.vlan', 'like', $like)
                    // cari customer/location/longlat hanya di tb_service (s)
                    ->orWhere('s.customer', 'like', $like)
                    ->orWhere('s.location', 'like', $like)
                    ->orWhere('s.longlat', 'like', $like)
                    ->orWhereRaw("COALESCE(srv2.service, srv1.service, s.service, ip.service) LIKE ?", [$like]);
            });
        }

        $data = $base->orderByRaw('INET_ATON(ip.ip) asc')->paginate(50)->appends(['search' => $search]);

        // kirim isEnterprise ke view agar view tahu branch mana yang dipakai
        return view('domain.detail', compact('data', 'domain', 'title', 'isEnterprise'));
    }
    public function export($id, Request $request)
    {
        $domain = DomainModel::findOrFail($id);
        $search = $request->input('search');

        // build same base query as show()
        $base = DB::table('tb_ip as ip')
            ->join('tb_vlan as vlan', 'ip.vlan', '=', 'vlan.id')
            ->join('tb_domain as domain', 'vlan.domain', '=', 'domain.id')
            ->leftJoin('tb_service as s', function ($join) {
                $join->on('ip.service', '=', 's.id')
                    ->orOn('ip.service', '=', 's.service');
            })
            ->leftJoin('service as srv1', 's.service', '=', 'srv1.id')
            ->leftJoin('service as srv2', 'ip.service', '=', 'srv2.id')
            ->where('ip.isdeleted', 0)
            ->where('vlan.isdeleted', 0)
            ->where('domain.isdeleted', 0)
            ->where('domain.id', $domain->id)
            ->select(
                'ip.device as device',
                'ip.devicecs as devicecs',
                'ip.ip as ip',
                'ip.ipcs as ipcs',
                'ip.vlanid as vlanid',
                'vlan.vlan as vlan_name',
                DB::raw("COALESCE(srv2.service, srv1.service, s.service, ip.service) as service_name"),
                's.customer as customer',
                'vlan.block_ip as block_ip',
                'vlan.gateway as gateway',
                's.location as service_location',
                's.longlat as longlat',
                'ip.rack as rack',
                'ip.bandwith as bandwith',
                'ip.location as location',
                'ip.r_number as r_number',
                'ip.b_number as b_number'
            );

        if ($search) {
            $like = "%{$search}%";
            $base = $base->where(function ($q) use ($like) {
                $q->where('ip.ip', 'like', $like)
                    ->orWhere('ip.device', 'like', $like)
                    ->orWhere('vlan.vlan', 'like', $like)
                    ->orWhere('s.customer', 'like', $like)
                    ->orWhere('s.location', 'like', $like)
                    ->orWhere('s.longlat', 'like', $like)
                    ->orWhereRaw("COALESCE(srv2.service, srv1.service, s.service, ip.service) LIKE ?", [$like]);
            });
        }

        $rows = $base->orderByRaw('INET_ATON(ip.ip) asc')->get();

        $filename = 'domain_' . ($domain->domain ?? $domain->id) . '_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // header row
            fputcsv($out, ['Device', 'Device CS', 'Vlan-ID', 'Vlan', 'IP', 'IP CS', 'Service', 'Customer', 'Block IP', 'Gateway', 'Location (Customer)', 'Longlat', 'Rack', 'Bandwith', 'Location', 'R Number', 'B Number']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->device ?? '-',
                    $r->devicecs ?? '-',
                    $r->vlanid ?? '-',
                    $r->vlan_name ?? '-',
                    $r->ip ?? '-',
                    $r->ipcs ?? '-',
                    $r->service_name ?? '-',
                    $r->customer ?? '-',
                    $r->block_ip ?? '-',
                    $r->gateway ?? '-',
                    $r->service_location ?? '-',
                    $r->longlat ?? '-',
                    $r->rack ?? '-',
                    $r->bandwith ?? '-',
                    $r->location ?? '-',
                    $r->r_number ?? '-',
                    $r->b_number ?? '-',
                ]);
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
