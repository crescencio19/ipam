@extends('layout.main')

@section('content')
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
 <form action="{{ url()->current() }}" method="GET" class="navbar-nav align-items-center">
  <div class="nav-item d-flex align-items-center">
    <i class="bx bx-search fs-4 lh-0"></i>
    <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Search" value="{{ $search ?? request('search') }}" />
  </div>
 </form>
</nav>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-1">{{ $title }} - {{ $vlan->vlanid ?? '' }} {{ $vlan->vlan ?? '' }}</h4>
  <p class="text-muted mb-3">Block IP range: {{ $ipRange }} @if($vlan->block_ip) (raw: {{ $vlan->block_ip }}) @endif</p>

  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Devices</th>
            <th>IP</th>
            <th>Vlan-ID</th>
            <th>Vlan Name</th>
            <th>Services</th>
            <th>Block IP</th>
            <th>Gateway</th>
            <th>Rack Server</th>
            <th>Location</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($data as $row)
            @php
              $hasService = !empty($row->service_name) || !empty($row->Service);
              $rowClass = $hasService ? 'table-danger' : 'table-success';
            @endphp
            <tr class="{{ $rowClass }}">
              <td>{{ ($data->currentPage()-1) * $data->perPage() + $loop->iteration }}</td>
              <td>{{ $row->device }}</td>
              <td>{{ $row->ip }}</td>
              <td>{{ $row->vlanid }}</td>
              <td>{{ $row->vlan_name }}</td>
              <td>{{ $row->service_name ?? $row->Service ?? '-' }}</td>
              <td>{{ $row->block_ip }}</td>
              <td>{{ $row->gateway }}</td>
              <td>{{ $row->rack ?? '-' }}</td>
              <td>{{ $row->location ?? '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="10" class="text-center">No IP data</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="mt-3 d-flex justify-content-end">
        {{ $data->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection