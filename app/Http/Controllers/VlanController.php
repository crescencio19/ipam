<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VlanModel;
use App\Models\DomainModel;
use App\Models\VlansSerModel;
use App\Models\IpModel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class VlanController extends Controller
{
    public function index(Request $request)
    {
        $searchRaw = $request->input('search', '');
        $search = trim($searchRaw);

        $query = VlanModel::where('isdeleted', 0);

        if ($search !== '') {
            $likeRaw = '%' . $search . '%';
            $likeLower = '%' . strtolower($search) . '%';

            // cari di kolom terpisah dan juga dalam satu concatenated field (lebih toleran terhadap format)
            $query->where(function ($q) use ($likeRaw, $likeLower) {
                $q->where('vlanid', 'like', $likeRaw)
                    ->orWhere('vlan', 'like', $likeRaw)
                    // case-insensitive pencarian untuk gateway / block_ip
                    ->orWhereRaw('LOWER(gateway) LIKE ?', [$likeLower])
                    ->orWhereRaw('LOWER(block_ip) LIKE ?', [$likeLower])
                    // fallback: gabungkan beberapa kolom jadi satu string untuk mencari pola yang mungkin terbagi
                    ->orWhereRaw("LOWER(CONCAT_WS(' ', IFNULL(vlanid,''), IFNULL(vlan,''), IFNULL(gateway,''), IFNULL(block_ip,''))) LIKE ?", [$likeLower])
                    ->orWhereHas('domainData', function ($q2) use ($likeLower) {
                        $q2->whereRaw('LOWER(domain) LIKE ?', [$likeLower])->where('isdeleted', 0);
                    });
            });
        }

        // debug log (hapus/komentari di produksi)
        \Log::info('VlanController::index search', [
            'search' => $search,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        $vlans = $query->orderBy('id', 'asc')->paginate(5)->appends(['search' => $searchRaw]);
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
            'gateway' => 'required',
            'block_ip' => 'required',
            'vlansser_id' => ['nullable', Rule::exists((new VlansSerModel)->getTable(), 'id')],
            // if not using vlansser_id ensure client sends vlan & vlanid (optional fallback)
        ]);

        // default from form (fallback)
        $vlanid = $request->input('vlanid');
        $vlan = $request->input('vlan');

        // if user selected a vlans_ser entry, override values
        if ($request->filled('vlansser_id')) {
            $vs = VlansSerModel::find($request->input('vlansser_id'));
            if ($vs) {
                $vlanid = $vs->vlanid;
                $vlan = $vs->vlan;
            }
        }

        VlanModel::create([
            'domain' => $request->domain,
            'vlan' => $vlan,
            'vlanid' => $vlanid,
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
            'gateway' => 'required',
            'block_ip' => 'required',
            'vlansser_id' => ['nullable', Rule::exists((new VlansSerModel)->getTable(), 'id')],
        ]);

        $vlanid = $request->input('vlanid');
        $vlan = $request->input('vlan');
        if ($request->filled('vlansser_id')) {
            $vs = VlansSerModel::find($request->input('vlansser_id'));
            if ($vs) {
                $vlanid = $vs->vlanid;
                $vlan = $vs->vlan;
            }
        }

        $vlanModel = VlanModel::findOrFail($id);
        $vlanModel->update([
            'domain' => $request->domain,
            'vlan' => $vlan,
            'vlanid' => $vlanid,
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
        $searchRaw = $request->input('search', '');
        $search = trim($searchRaw);
        $domainId = $request->input('domain'); // domain filter (nullable)

        $query = VlanModel::with('domainData')->where('isdeleted', 0);

        // filter by domain id if provided
        if (!empty($domainId)) {
            $query->where('domain', $domainId);
        }

        if ($search !== '') {
            $like = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(vlanid) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(vlan) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(block_ip) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(gateway) LIKE ?', [$like]);
            })->orWhereHas('domainData', function ($q) use ($like) {
                $q->whereRaw('LOWER(domain) LIKE ?', [$like])->where('isdeleted', 0);
            });
        }

        $vlans = $query->orderBy('vlanid')->paginate(20)->appends(['search' => $searchRaw, 'domain' => $domainId]);
        // hanya domain yang punya vlan (aktif)
        $domainIds = VlanModel::where('isdeleted', 0)
            ->whereNotNull('domain')
            ->pluck('domain')
            ->unique()
            ->toArray();
        $domains = DomainModel::whereIn('id', $domainIds)
            ->where('isdeleted', 0)
            ->orderBy('domain')
            ->get();

        // ambil daftar vlan.id yang sudah punya IP (untuk menandai warna)
        $vlansWithIp = IpModel::where('isdeleted', 0)
            ->whereNotNull('vlan')
            ->pluck('vlan')
            ->unique()
            ->map(function ($v) {
                return (int) $v;
            }) // pastikan integer
            ->toArray();

        $title = 'Vlan Ranges';
        return view('dasvlan', compact('vlans', 'title', 'search', 'domains', 'domainId', 'vlansWithIp'));
    }
}
