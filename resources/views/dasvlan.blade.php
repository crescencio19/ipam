@extends('layout.main')

@section('content')
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
  <form action="{{ url()->current() }}" method="GET" class="navbar-nav align-items-center">
    <div class="nav-item d-flex align-items-center">
      <i class="bx bx-search fs-4 lh-0"></i>
      <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Search" value="{{ request('search') }}" />
    </div>
  </form>
</nav>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">{{ $title ?? 'Dashboard VLAN' }}</h4>

  {{-- Alert jika melakukan pencarian tapi tidak ada hasil --}}
  @if(request('search') && ( (isset($vlans) && method_exists($vlans, 'total') ? $vlans->total() == 0 : (isset($vlans) ? $vlans->count() == 0 : true)) ))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <strong>No search results found.</strong>
      <div>Search: "{{ request('search') }}"</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="d-flex flex-wrap">
    @foreach($vlans as $vlan)
      @php
        $domainName = $vlan->domainData->domain ?? 'Unknown';
        $dn = strtolower($domainName);

        if (str_contains($dn, 'enterprise')) {
          $ipRange = '100 - 200';
        } elseif (str_contains($dn, 'ran')) {
          $ipRange = '300 - 400';
        } elseif (str_contains($dn, 'intra')) {
          $ipRange = '401 - 500';
        } elseif (str_contains($dn, 'vcore')) {
          $ipRange = '600 - 700';
        } elseif (str_contains($dn, 'datacom')) {
          $ipRange = '701 - 900';
        } elseif (str_contains($dn, 'datacenter')) {
          $ipRange = '901 - 1000';
        } else {
          $ipRange = '-';
        }
      @endphp

      <div class="card m-2" style="width: 20rem;">
        <a href="{{ route('vlan.show', $vlan->id) }}" class="text-decoration-none text-reset">
        <div class="card-body">
           <h5 class="card-title">{{ $vlan->vlanid ?? '-' }} - {{ $vlan->vlan ?? '-' }}</h5>
           <p class="mb-1"><strong>Domain:</strong>
             @if($vlan->domain)
               <a href="{{ route('domain.show', $vlan->domain) }}">{{ $domainName }}</a>
             @else
               {{ $domainName }}
             @endif
           </p>
           <p class="mb-1"><strong>IP Range:</strong> {{ $ipRange }}</p>
           <p class="mb-1"><strong>Block IP:</strong> {{ $vlan->block_ip ?? '-' }}</p>
           <p class="mb-0"><strong>Gateway:</strong> {{ $vlan->gateway ?? '-' }}</p>
        </div>
        </a>
      </div>
    @endforeach
  </div>

  <div class="mt-3 d-flex justify-content-end">
    @if(isset($vlans) && method_exists($vlans, 'links'))
      {{ $vlans->links('pagination::bootstrap-5') }}
    @endif
  </div>
</div>
@endsection