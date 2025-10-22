<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\IpController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\VlansSerController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\RackController;


// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [HomeController::class, 'index']);

Route::get('/domain/{id}', [HomeController::class, 'show'])->name('domain.show');

Route::get('/search', [DomainController::class, 'search'])->name('search');
Route::get('/domain', [DomainController::class, 'index'])->name('domain.domain');
Route::get('/domain/create', [DomainController::class, 'create'])->name('domain.create');
Route::post('/domain/store', [DomainController::class, 'store'])->name('domain.store');
Route::get('/domain/edit/{id}', [DomainController::class, 'edit'])->name('domain.edit');
Route::post('/domain/update/{id}', [DomainController::class, 'update'])->name('domain.update');
Route::post('/domain/destroy/{id}', [DomainController::class, 'destroy'])->name('domain.destroy');

Route::get('/service', [ServiceController::class, 'index'])->name('service.service');
Route::get('/service/create', [ServiceController::class, 'create'])->name('service.create');
Route::post('/service/store', [ServiceController::class, 'store'])->name('service.store');
Route::get('/service/edit/{id}', [ServiceController::class, 'edit'])->name('service.edit');
Route::put('/service/update/{id}', [ServiceController::class, 'update'])->name('service.update');
Route::post('/service/destroy/{id}', [ServiceController::class, 'destroy'])->name('service.destroy');

Route::get('/service/by-domain', [ServiceController::class, 'byDomain'])->name('service.byDomain');

Route::get('/vlan', [VlanController::class, 'index'])->name('vlan.vlan');
Route::post('/vlan/store', [VlanController::class, 'store'])->name('vlan.store');
Route::get('/vlan/edit/{id}', [VlanController::class, 'edit'])->name('vlan.edit');
Route::put('/vlan/update/{id}', [VlanController::class, 'update'])->name('vlan.update');
Route::post('/vlan/destroy/{id}', [VlanController::class, 'destroy'])->name('vlan.destroy');

// detail view for a VLAN (card -> detail list)
Route::get('/vlan/{id}', [VlanController::class, 'show'])->name('vlan.show');


// optional: dashboard route if you want separate dashboard view
Route::get('/dasvlan', [VlanController::class, 'dashboard'])->name('vlan.dashboard');


Route::get('/ip', [IpController::class, 'index'])->name('ip.ip');
Route::post('/ip/store', [IpController::class, 'store'])->name('ip.store');
Route::get('/ip/create', [IpController::class, 'create'])->name('ip.create');
Route::get('/ip/edit/{id}', [IpController::class, 'edit'])->name('ip.edit   ');
Route::put('/ip/update/{id}', [IpController::class, 'update'])->name('ip.update');
Route::post('/ip/destroy/{id}', [IpController::class, 'destroy'])->name('ip.destroy');
Route::get('/ip/getVlanDetails/{vlanId}', [IpController::class, 'getVlanDetails'])->name('ip.getVlanDetails');
Route::get('/ip/data', [IpController::class, 'data'])->name('ip.data');

Route::get('/command', [CommandController::class, 'index'])->name('command.command');
Route::get('/command/create', [CommandController::class, 'create'])->name('command.create');
Route::post('/command/store', [CommandController::class, 'store'])->name('command.store');
Route::get('/command/edit/{id}', [CommandController::class, 'edit'])->name('command.edit');
Route::put('/command/update/{id}', [CommandController::class, 'update'])->name('command.update');
Route::post('/command/destroy/{id}', [CommandController::class, 'destroy'])->name('command.destroy');
Route::get('/services', [ServicesController::class, 'index'])->name('services.services');
Route::get('/services/create', [ServicesController::class, 'create'])->name('services.create');
Route::post('/services/store', [ServicesController::class, 'store'])->name('services.store');
Route::get('/services/edit/{id}', [ServicesController::class, 'edit'])->name('services.edit');
Route::put('/services/update/{id}', [ServicesController::class, 'update'])->name('services.update');
Route::post('/services/destroy/{id}', [ServicesController::class, 'destroy'])->name('services.destroy');

Route::get('/generate', [GenerateController::class, 'index'])->name('generate.generate');

Route::get('/vlansser', [VlansSerController::class, 'index'])->name('vlansser.vlansser');
Route::get('/vlansser/create', [VlansSerController::class, 'create'])->name('vlansser.create');
Route::post('/vlansser/store', [VlansSerController::class, 'store'])->name('vlansser.store');
Route::get('/vlansser/edit/{id}', [VlansSerController::class, 'edit'])->name('vlansser.edit');
Route::put('/vlansser/update/{id}', [VlansSerController::class, 'update'])->name('vlansser.update');
Route::post('/vlansser/destroy/{id}', [VlansSerController::class, 'destroy'])->name('vlansser.destroy');
Route::get('/vlansser/by-domain', [VlansSerController::class, 'byDomain'])->name('vlansser.byDomain');

Route::get('/device', [DeviceController::class, 'index'])->name('device.device');
Route::get('/device/create', [DeviceController::class, 'create'])->name('device.create');
Route::post('/device/store', [DeviceController::class, 'store'])->name('device.store');
Route::get('/device/edit/{id}', [DeviceController::class, 'edit'])->name('device.edit');
Route::put('/device/update/{id}', [DeviceController::class, 'update'])->name('device.update');
Route::post('/device/destroy/{id}', [DeviceController::class, 'destroy'])->name('device.destroy');
Route::get('/device/by-domain', [DeviceController::class, 'byDomain'])->name('device.byDomain');

Route::get('/rack', [RackController::class, 'index'])->name('rack.rack');
Route::get('/rack/create', [RackController::class, 'create'])->name('rack.create');
Route::post('/rack/store', [RackController::class, 'store'])->name('rack.store');
Route::get('/rack/edit/{id}', [RackController::class, 'edit'])->name('rack.edit');
Route::put('/rack/update/{id}', [RackController::class, 'update'])->name('rack.update');
Route::post('/rack/destroy/{id}', [RackController::class, 'destroy'])->name('rack.destroy');
Route::get('/rack/by-domain', [RackController::class, 'byDomain'])->name('rack.byDomain');
