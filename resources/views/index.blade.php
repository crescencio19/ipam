@extends('layout.main')
@section('content')

<div class="d-flex flex-wrap">
  <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <form action="{{ url()->current() }}" method="GET" class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
        <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Search" value="{{ request('search') }}" />
      </div>
    </form>
  </nav>

@php
  $noResults = request('search') && ( (isset($domains) && method_exists($domains, 'total') ? $domains->total() == 0 : (isset($domains) ? $domains->count() == 0 : true)) );
@endphp

@if($noResults)
    {{-- kosong: tetap tampilkan area kosong, modal akan muncul otomatis --}}
@endif

@foreach ($domains as $item)
    @php
      $isEnterprise = str_contains(strtolower($item->domain ?? ''), 'enterprise');
    @endphp

    <div class="card m-2" style="width: 18rem;">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="../assets/img/icons/unicons/chart-success.png" alt="chart success" class="rounded">
          </div>
        </div>

        <!-- Baris: Domain + badge -->
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="fw-semibold">Domain</span>
          <div class="d-flex flex-column align-items-end">
            <span class="badge bg-info text-dark mb-1">VLAN: {{ $item->vlan_count }}</span>
            <span class="badge bg-primary">IP: {{ $item->ip_count }}</span>
          </div>
        </div>

        <!-- Judul domain -->
        <h5 class="card-title mb-0">
          <a href="{{ route('domain.show', $item->id) }}">{{ $item->domain }}</a>
        </h5>

        @if($isEnterprise)
          
        @endif

      </div>
    </div>
@endforeach

</div>

@if($noResults)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var modalEl = document.getElementById('noResultModal');
      if (modalEl) {
        var bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    });
  </script>
@endif

@endsection
