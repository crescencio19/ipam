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
            $query->where(function ($q) use ($search) {
                $q->where('service_command', 'like', "%{$search}%")
                    ->orWhere('command', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })->orWhereHas('ipData', function ($q) use ($search) {
                $q->where('ip', 'like', "%{$search}%")
                    ->orWhere('device', 'like', "%{$search}%");
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

        CommandModel::create([
            'ip' => $request->ip,
            'service_command' => $request->service_command,
            'command' => $request->command,
            'description' => $request->description,
            'remark' => $request->remark,
            'iby' => auth()->user()->username ?? null,
            'idt' => now(),
            'isdeleted' => 0,
        ]);

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

        $command = CommandModel::findOrFail($id);
        $command->update([
            'ip' => $request->ip,
            'service_command' => $request->service_command,
            'command' => $request->command,
            'description' => $request->description,
            'remark' => $request->remark,
            'uby' => auth()->user()->username ?? null,
            'udt' => now(),
        ]);

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
