<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RackModel;
use App\Models\DomainModel;


class RackController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // build query (do not ->get() yet) so we can order and paginate on the query builder
        $query = RackModel::where('isdeleted', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rack', 'like', "%{$search}%")
                    ->where('domain', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $racks = $query->orderBy('rack', 'asc')->paginate(5)->appends(['search' => $search]);
        $domains = DomainModel::where('isdeleted', 0)->get();
        $title = 'Racks';
        return view('rack.rack', compact('racks', 'domains', 'title'));
    }
    public function create()
    {
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('rack.create', compact('domains'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'rack' => 'required',
            'description' => 'required',
            'domain' => 'required',
        ]);
        RackModel::create(
            [
                'rack' => $request->rack,
                'description' => $request->description,
                'domain' => $request->domain,
            ]
        );
        return redirect()->route('rack.rack')->with('success', 'Rack created successfully.');
    }
    public function edit($id)
    {
        $rack = RackModel::findOrFail($id);
        $domains = DomainModel::where('isdeleted', 0)->get();
        return view('rack.edit', compact('rack', 'domains'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'rack' => 'required',
            'description' => 'required',
            'domain' => 'required',
        ]);
        $rack = RackModel::findOrFail($id);
        $rack->update([
            'rack' => $request->rack,
            'description' => $request->description,
            'domain' => $request->domain,
        ]);
        return redirect()->route('rack.rack')->with('success', 'Rack updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $rack = RackModel::findOrFail($id);
        $rack->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);
        return redirect()->route('rack.rack')->with('success', 'Rack deleted successfully.');
    }
    public function byDomain(Request $request)
    {
        $domain = $request->input('domain');
        if (!$domain) return response()->json([]);
        $list = \App\Models\RackModel::where('isdeleted', 0)
            ->where('domain', $domain)
            ->orderBy('rack')
            ->get(['rack']);
        return response()->json($list);
    }
}
