<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VlansSerModel;
use App\Models\DomainModel;

class VlansSerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // build query (do not ->get() yet) so we can order and paginate on the query builder
        $query = VlansSerModel::where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vlanid', 'like', "%{$search}%")
                    ->orWhere('vlan', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $vlans = $query->orderBy('vlanid', 'asc')->paginate(5)->appends(['search' => $search]);
        $domains = DomainModel::where('isdeleted', 0)->get();
        $title = 'VlansSer';
        return view('vlansser.vlansser', compact('vlans', 'domains', 'title'));
    }

    public function create()
    {
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('vlansser.create', compact('domains'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'vlanid' => 'required',
            'vlan' => 'required',
            'description' => 'required',
            'domain' => 'required',
        ]);

        VlansSerModel::create(
            [
                'vlanid' => $request->vlanid,
                'vlan' => $request->vlan,
                'description' => $request->description,
                'domain' => $request->domain,
            ]
        );

        return redirect()->route('vlansser.vlansser')->with('success', 'VLAN Service created successfully.');
    }

    public function edit($id)
    {
        $vlan = VlansSerModel::findOrFail($id);
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('vlansser.edit', compact('vlan', 'domains'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'vlanid' => 'required',
            'vlan' => 'required',
            'description' => 'required',
            'domain' => 'required',
        ]);

        $vlan = VlansSerModel::findOrFail($id);
        $vlan->update([
            'vlanid' => $request->vlanid,
            'vlan' => $request->vlan,
            'description' => $request->description,
            'domain' => $request->domain,
        ]);

        return redirect()->route('vlansser.vlansser')->with('success', 'VLAN Service updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $vlan = VlansSerModel::findOrFail($id);
        $vlan->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);

        return redirect()->route('vlansser.vlansser')->with('success', 'VLAN Service deleted successfully.');
    }

    // new AJAX endpoint: return vlans_ser for a domain
    public function byDomain(Request $request)
    {
        $domainId = $request->input('domain');
        if (empty($domainId)) {
            return response()->json([]);
        }

        $list = VlansSerModel::where('isdeleted', 0)
            ->where('domain', $domainId)
            ->orderBy('vlanid', 'asc')
            ->get(['id', 'vlanid', 'vlan']);

        // return as array of {id, text}
        $result = $list->map(function ($r) {
            return [
                'id' => $r->id,
                'vlanid' => $r->vlanid,
                'vlan' => $r->vlan,
                'text' => "{$r->vlanid} — {$r->vlan}",
            ];
        })->values();

        return response()->json($result);
    }
}
