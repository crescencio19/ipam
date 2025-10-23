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
 Domain
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
           Domain
              @if(isset($domain) && !empty($domain->domain))
                <small class="text-muted"> — {{ $domain->domain }}</small>
              @endif
            </h5>
            <div class="d-flex align-items-center">
              <select id="serviceFilter" class="form-select form-select-sm" style="min-width:200px; margin-right:8px;">
                <option value="all">All IPs</option>
                <option value="with">With Service</option>
                <option value="without">Without Service</option>
              </select>

              <!-- Export CSV button -->
              <a href="{{ route('domain.export', ['id' => $domain->id, 'search' => request('search')]) }}"
                 id="exportCsvBtn"
                 data-url="{{ route('domain.export', ['id' => $domain->id, 'search' => request('search')]) }}"
                 class="btn btn-outline-primary btn-sm"
                 title="Export CSV (Excel)">
                <i class="bx bx-download"></i> Export
              </a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Device</th>
                        <th>Device CS</th>
                        <th>Vlan-ID</th>
                        <th>Vlan</th>
                        <th>IP</th>
                        <th>IP CS</th>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Block IP</th>
                        <th>Gateway</th>
                        <th>Location (Customer)</th>
                        <th>Longlat</th>
                        <th>Rack</th>
                        <th>Bandwith</th>
                        <th>Location</th>
                        <th>R Number</th>
                        <th>B Number</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($data as $row)
                        @php
                            $device = $row->device ?? $row->Device ?? '-';
                            $devicecs = $row->devicecs ?? $row->DeviceCS ?? '-';
                            $vlanid = $row->vlanid ?? $row->VLANID ?? '-';
                            $vlanname = $row->vlan_name ?? $row->VLANNAME ?? $row->vlan ?? '-';
                            $ip = $row->ip ?? $row->IP ?? '-';
                            $ipcs = $row->ipcs ?? $row->IPCS ?? '-';
                            $serviceLabel = $row->service_name ?? $row->Service ?? $row->service ?? '-';
                            $customer = $row->customer ?? $row->nama_customer ?? '-';
                            $block_ip = $row->block_ip ?? $row->BlockIP ?? '-';
                            $gateway = $row->gateway ?? $row->Gateway ?? '-';
                            $service_location = $row->service_location ?? $row->serviceLocation ?? $row->location_customer ?? '-';
                            $longlat = $row->longlat ?? $row->long_lat ?? '-';
                            $rack = $row->rack ?? $row->Rack ?? '-';
                            $bandwith = $row->bandwith ?? $row->bandwidth ?? $row->Bandwith ?? '-';
                            $location = $row->location ?? $row->Lokasi ?? $row->ip_location ?? '-';
                            $r_number = $row->r_number ?? $row->RNumber ?? '-';
                            $b_number = $row->b_number ?? $row->BNumber ?? '-';
                            $hasService = !empty(trim((string)$serviceLabel)) && $serviceLabel !== '-';
                            $rowClass = $hasService ? 'table-danger' : 'table-success';
                        @endphp
                        <tr class="{{ $rowClass }}" data-has-service="{{ $hasService ? '1' : '0' }}">
                            <td>{{ (isset($data) && method_exists($data, 'currentPage')) ? (($data->currentPage() - 1) * $data->perPage() + $loop->iteration) : $loop->iteration }}</td>
                            <td>{{ $device }}</td>
                            <td>{{ $devicecs }}</td>
                            <td>{{ $vlanid }}</td>
                            <td>{{ $vlanname }}</td>
                            <td>{{ $ip }}</td>
                            <td>{{ $ipcs }}</td>
                            <td>{{ $serviceLabel }}</td>
                            <td>{{ $customer }}</td>
                            <td>{{ $block_ip }}</td>
                            <td>{{ $gateway }}</td>
                            <td>{{ $service_location }}</td>
                            <td>{{ $longlat }}</td>
                            <td>{{ $rack }}</td>
                            <td>{{ $bandwith }}</td>
                            <td>{{ $location }}</td>
                            <td>{{ $r_number }}</td>
                            <td>{{ $b_number }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="text-center">There is no IP data for this domain.</td>
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
  if (filter) {
    filter.addEventListener('change', function () {
      const val = this.value;
      document.querySelectorAll('tbody.table-border-bottom-0 tr').forEach(function (tr) {
        if (tr.querySelector('td') && tr.querySelector('td').getAttribute('colspan')) return;
        const has = tr.getAttribute('data-has-service') === '1';
        if (val === 'all') tr.style.display = '';
        else if (val === 'with') tr.style.display = has ? '' : 'none';
        else if (val === 'without') tr.style.display = has ? 'none' : '';
      });
    });
  }

  // build header -> index map (robust for enterprise vs non-enterprise layouts)
  const headerMap = {};
  document.querySelectorAll('table thead th').forEach((th, i) => {
    const key = (th.textContent || '').trim().toLowerCase().replace(/\s+/g, ' ');
    headerMap[key] = i;
  });

  function cellTextByHeader(tr, headerNameVariants) {
    for (const name of headerNameVariants) {
      const key = name.toLowerCase();
      if (key in headerMap) {
        const idx = headerMap[key];
        return (tr.children[idx] && tr.children[idx].textContent || '').trim().toLowerCase();
      }
    }
    return '';
  }

  const searchInput = document.querySelector('input[name="search"]');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.toLowerCase();
      document.querySelectorAll('tbody.table-border-bottom-0 tr').forEach(tr => {
        if (tr.querySelector('td[colspan]')) return;

        const device = cellTextByHeader(tr, ['device']);
        const ip = cellTextByHeader(tr, ['ip']);
        const vlanId = cellTextByHeader(tr, ['vlan-id','vlan id','vlanid']);
        const vlanName = cellTextByHeader(tr, ['vlan name','vlan']);
        const service = cellTextByHeader(tr, ['services','service']);
        const blockIp = cellTextByHeader(tr, ['block ip','block_ip','blockip']);
        const rack = cellTextByHeader(tr, ['rack server','rack']);
        const location = cellTextByHeader(tr, ['location','lokasi']);

        // enterprise/customer related fields
        const customer = cellTextByHeader(tr, ['customer','nama customer','nama_customer']);
        const bandwith = cellTextByHeader(tr, ['bandwith','bandwidth']);
        const longlat = cellTextByHeader(tr, ['longlat','long_lat','long lat']);
        const serviceLocation = cellTextByHeader(tr, ['location customer','service location','service_location','serviceLocation']);

        const hay = [device, ip, vlanId, vlanName, service, blockIp, rack, location, customer, bandwith, longlat, serviceLocation];
        const match = hay.some(s => s.includes(q));
        tr.style.display = match ? '' : 'none';
      });
    });
  }

  // Export tanpa reload
  const exportBtn = document.getElementById('exportCsvBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const url = this.dataset.url;
      if (!url) return;
      // fetch stream as blob and trigger download
      fetch(url, { credentials: 'same-origin' })
        .then(response => {
          if (!response.ok) throw new Error('Export failed');
          const disp = response.headers.get('Content-Disposition') || response.headers.get('content-disposition') || '';
          return response.blob().then(blob => ({ blob, disp }));
        })
        .then(({ blob, disp }) => {
          let filename = 'export.csv';
          const m = disp.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (m && m[1]) filename = m[1].replace(/['"]/g, '');
          const blobUrl = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = blobUrl;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          a.remove();
          window.URL.revokeObjectURL(blobUrl);
        })
        .catch(err => {
          console.error(err);
          // fallback: lakukan navigasi normal kalau fetch gagal
          window.location = url;
        });
    });
  }
});
</script>
@endsection
