<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VlanController;
use App\Http\Controllers\IpController;


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
Route::post('/service/update/{id}', [ServiceController::class, 'update'])->name('service.update');
Route::post('/service/destroy/{id}', [ServiceController::class, 'destroy'])->name('service.destroy');


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
