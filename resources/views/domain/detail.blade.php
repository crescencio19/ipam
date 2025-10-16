@extends('layout.main')

@section('content')
@php
  // pilih label yang akan ditampilkan di title:
  // prioritas: domain -> vlan -> fallback $title
  $entityLabel = null;
  if (isset($domain) && !empty($domain->domain)) {
      $entityLabel = $domain->domain;
  } elseif (isset($vlan) && !empty($vlan->vlan)) {
      // bisa tampilkan vlanid + vlan name
      $entityLabel = ($vlan->vlanid ? ($vlan->vlanid . ' - ') : '') . ($vlan->vlan ?? $vlan->name ?? '');
  }
@endphp

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
  <form action="{{ url()->current() }}" method="GET" class="navbar-nav align-items-center">
    <div class="nav-item d-flex align-items-center">
      <i class="bx bx-search fs-4 lh-0"></i>
      <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Search" value="{{ request('search') }}" />
    </div>
  </form>
</nav>

<h4 class="fw-bold py-3 mb-4">
  {{ $title ?? 'Detail' }}
  @if($entityLabel)
    <small class="text-muted"> — {{ $entityLabel }}</small>
  @endif
</h4>

@php
  // fallback: jika controller mengirim $isIntra, gunakan negasinya; jika tidak, tetap pakai $isEnterprise jika ada
  $isEnterprise = $isEnterprise ?? (isset($isIntra) ? !$isIntra : (isset($domain) ? (stripos($domain->domain ?? '', 'enterprise') !== false) : false));
  $baseCols = 10;
  $colCount = $baseCols + ($isEnterprise ? 3 : 0);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Alert jika melakukan pencarian tapi tidak ada hasil pada detail domain --}}
    @if(request('search') && ( (isset($data) && method_exists($data, 'total') ? $data->total() == 0 : (isset($data) ? $data->count() == 0 : true)) ))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Hasil pencarian tidak ditemukan pada domain ini.</strong>
        <div>Pencarian: "{{ request('search') }}"</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Basic Bootstrap Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              {{ $title }}
              @if(isset($domain) && !empty($domain->domain))
                <small class="text-muted"> — {{ $domain->domain }}</small>
              @endif
            </h5>
            <div>
              <select id="serviceFilter" class="form-select form-select-sm" style="min-width:200px;">
                <option value="all">All IPs</option>
                <option value="with">With Service</option>
                <option value="without">Without Service</option>
              </select>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        {{-- <th>Domain</th> --}}
                        <th>Device</th>
                        <th>IP</th>
                        <th>Vlan-ID</th>
                        <th>Vlan Name</th>
                        <th>Services</th>
                        <th>Block IP</th>
                        <th>Gateway</th>
                        @if(!$isEnterprise)
                          <th>Rack Server</th>
                          <th>Location</th>
                        @endif
                        @if($isEnterprise)
                          <th>Customer</th>
                          <th>Bandwidth</th>
                          <th>Longlat</th>
                          <th>Location Customer</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($data as $row)
                        @php
                            $serviceLabel = $row->service_name ?? $row->Service ?? null;
                            $hasService = !empty($serviceLabel);
                            $rowClass = $hasService ? 'table-danger' : 'table-success';
                        @endphp
                        <tr class="{{ $rowClass }}" data-has-service="{{ $hasService ? '1' : '0' }}">
                            <td>{{ (isset($data) && method_exists($data, 'currentPage')) ? (($data->currentPage() - 1) * $data->perPage() + $loop->iteration) : $loop->iteration }}</td>
                            
                            <td>{{ $row->device ?? $row->Device ?? '-' }}</td>
                            <td>{{ $row->ip ?? $row->IP ?? '-' }}</td>
                            <td>{{ $row->vlanid ?? $row->VLANID ?? '-' }}</td>
                            <td>{{ $row->vlan_name ?? $row->VLANNAME ?? $row->vlan ?? '-' }}</td>
                            <td>{{ $serviceLabel ?? '-' }}</td>
                            <td>{{ $row->block_ip ?? $row->BlockIP ?? '-' }}</td>
                            <td>{{ $row->gateway ?? $row->Gateway ?? '-' }}</td>
                            @if(!$isEnterprise)
                              <td>{{ $row->rack ?? $row->Rack ?? '-' }}</td>
                              <td>{{ $row->location ?? $row->Lokasi ?? $row->Location ?? '-' }}</td>
                            @endif

                            @if($isEnterprise)
                              <td>{{ $row->customer ?? $row->nama_customer ?? $row->NamaCustomer ?? '-' }}</td>
                              <td>{{ $row->bandwith ?? $row->bandwidth ?? $row->Bandwith ?? '-' }}</td>
                              <td>{{ $row->longlat ?? $row->long_lat ?? $row->Longlat ?? '-' }}</td>
                              {{-- Location Customer: ambil dari tabel service --}}
                              <td>{{ $row->service_location ?? $row->serviceLocation ?? '-' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colCount }}" class="text-center">There is no IP data for this domain.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-end">
                @if(isset($data) && method_exists($data, 'links'))
                  {{ $data->links('pagination::bootstrap-5') }}
                @endif
            </div>
        </div>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const filter = document.getElementById('serviceFilter');
  if (!filter) return;
  filter.addEventListener('change', function () {
    const val = this.value;
    document.querySelectorAll('tbody.table-border-bottom-0 tr').forEach(function (tr) {
      // ignore empty-row (no data) which has colspan message
      if (tr.querySelector('td') && tr.querySelector('td').getAttribute('colspan')) return;
      const has = tr.getAttribute('data-has-service') === '1';
      if (val === 'all') {
        tr.style.display = '';
      } else if (val === 'with') {
        tr.style.display = has ? '' : 'none';
      } else if (val === 'without') {
        tr.style.display = has ? 'none' : '';
      }
    });
  });
});
</script>
@endsection
