<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommandModel;
use App\Models\IpModel;

class CommandController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // eager load ipData for view and use relation in search
        $query = CommandModel::with('ipData')->where('isdeleted', 0);

        if ($search) {
            // group all search conditions (including relation) together
            $query->where(function ($q) use ($search) {
                $q->where('service_command', 'like', "%{$search}%")
                    ->orWhere('command', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('ipData', function ($q2) use ($search) {
                        $q2->where('ip', 'like', "%{$search}%")
                            ->orWhere('device', 'like', "%{$search}%")
                            // also match combined "ip - device"
                            ->orWhereRaw("CONCAT(ip, ' - ', device) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        $commands = $query->orderBy('id', 'desc')->paginate(5)->appends(['search' => $search]);


        $title = 'Command List';
        // eager load ServiceData supaya blade dapat membaca $ip->ServiceData->service
        $ips = IpModel::with('ServiceData')->where('isdeleted', 0)->get();

        return view('command.command', compact('commands', 'title', 'ips', 'search'));
    }

    public function create()
    {
        $ips = IpModel::where('isdeleted', 0)->get();
        return view('command.create', compact('ips'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip' => 'required',
            'service_command' => 'required',
            'command' => 'required',
            // 'description' => 'required',
        ]);

        // pastikan selalu array (mendukung single atau multiple)
        $ips = $request->input('ip');
        if (!is_array($ips)) {
            $ips = [$ips];
        }

        foreach ($ips as $ipId) {
            CommandModel::create([
                'ip' => $ipId,
                'service_command' => $request->service_command,
                'command' => $request->command,
                'description' => $request->description,
                'remark' => $request->remark,
                'iby' => auth()->user()->username ?? null,
                'idt' => now(),
                'isdeleted' => 0,
            ]);
        }

        return redirect()->route('command.command')->with('success', 'Command created successfully.');
    }

    public function edit($id)
    {
        $command = CommandModel::findOrFail($id);
        $ips = IpModel::where('isdeleted', 0)->get();
        return view('command.edit', compact('command', 'ips'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'ip' => 'required',
            'service_command' => 'required',
            'command' => 'required',
            // 'description' => 'required',
        ]);

        $ips = $request->input('ip');
        if (!is_array($ips)) {
            $ips = [$ips];
        }

        $command = CommandModel::findOrFail($id);

        // update record utama dengan IP pertama
        $firstIp = array_shift($ips); // ambil IP pertama, sisanya untuk dibuat sebagai record baru
        $command->update([
            'ip' => $firstIp,
            'service_command' => $request->service_command,
            'command' => $request->command,
            'description' => $request->description,
            'remark' => $request->remark,
            'uby' => auth()->user()->username ?? null,
            'udt' => now(),
        ]);

        // buat record baru untuk IP tambahan (jika ada)
        foreach ($ips as $ipId) {
            CommandModel::create([
                'ip' => $ipId,
                'service_command' => $request->service_command,
                'command' => $request->command,
                'description' => $request->description,
                'remark' => $request->remark,
                'iby' => auth()->user()->username ?? null,
                'idt' => now(),
                'isdeleted' => 0,
            ]);
        }

        return redirect()->route('command.command')->with('success', 'Command updated successfully.');
    }
    public function destroy(Request $request, $id)
    {
        $command = CommandModel::findOrFail($id);
        $command->update([
            'isdeleted' => 1,
            'remark' => $request->remark, // Ambil dari input user
            // 'dby' => auth()()->name,
            'ddt' => now(),
        ]);

        return redirect()->route('command.command')->with('success', 'Command deleted successfully.');
    }
}
