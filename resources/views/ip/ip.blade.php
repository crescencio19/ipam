@extends('layout.main')
@section('search-action', route('ip.ip'))

@section('content')
<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
 <form action="{{ route('ip.ip') }}" method="GET" class="navbar-nav align-items-center">
  <div class="nav-item d-flex align-items-center">
    <i class="bx bx-search fs-4 lh-0"></i>
    <input
      type="text"
      name="search"
      class="form-control border-0 shadow-none"
      placeholder="Search"
      value="{{ request('search') }}"
    />
  </div>
 </form>
</nav>
 
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">{{ $title }}</h4>
    <!-- Basic Bootstrap Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $title }}</h5>
            
            <button type="button" class="btn rounded-pill btn-outline-primary" data-bs-toggle="modal" data-bs-target="#basicModal">
                              <span class="tf-icons bx bx-plus"></span>&nbsp; Add 
                            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Device</th>
                        <th>IP</th>
                        <th>VlanID</th>
                        <th>Vlan</th>
                        <th>IP Services</th>
                        <th>Rack</th>
                        <th>Bandwith</th>
                        <th>Location</th>
                        {{-- <th>Actions</th> --}}
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($ips as $item) 
                    @php
                      // tentukan label service terlebih dahulu
                      $serviceLabel = '-';
                      if (!empty($item->service_name)) {
                        $serviceLabel = trim($item->service_name);
                      } elseif (!empty($item->service) && isset($services)) {
                        $s = is_array($services) ? collect($services)->firstWhere('id', $item->service) : $services->firstWhere('id', $item->service);
                        $serviceLabel = $s->service ?? '-';
                      }

                      // jika label '-' atau kosong => anggap tidak ada service (hijau),
                      // jika ada label nyata => ada service (merah)
                      $hasServiceFinal = strlen(trim((string)$serviceLabel)) > 0 && $serviceLabel !== '-';
                      $rowClass = $hasServiceFinal ? 'table-danger' : 'table-success';
                    @endphp
                    <tr class="{{ $rowClass }}">
                            <td>{{ ($ips->currentPage() - 1) * $ips->perPage() + $loop->iteration }}</td>
                            <td>{{ $item->device }}</td>
                            <td>{{ $item->ip }}</td>
                            <td>{{ $item->vlanid_value ?? $item->vlanid ?? '-' }}</td>
                            <td>{{ $item->vlan_name ?? '-' }}</td>
                        <td>{{ $serviceLabel }}</td>
                         <td>{{ $item->rack ?? '-' }}</td>
                         <td>{{ $item->bandwith ?? '-' }}</td>
                         <td>{{ $item->location ?? '-' }}</td>
                         <td>
                             <!-- Tombol Edit -->
                             <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                 <i class='bx bx-edit'></i>
                             </button>
                            <!-- Tombol Delete -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Edit Service -->
                   <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('ip.update', $item->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit IP</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            
                  
           <div class="mb-3">
                <label for="vlan" class="form-label">Vlan (VlanId - Name)</label>
                <select class="form-select edit-vlan" name="vlan" id="vlan_{{ $item->id }}" aria-label="Default select example" data-item="{{ $item->id }}">
                  <option value="">Select Vlan</option>
                  @foreach ($vlans as $vlan)
                    <option value="{{ $vlan->id }}" data-vlanid="{{ $vlan->vlanid ?? '' }}" data-domain="{{ $vlan->domainData->domain ?? '' }}" {{ ($item->vlan == $vlan->id) ? 'selected' : '' }}>
                      {{ ($vlan->vlanid ?? '') }} - {{ $vlan->vlan }} - {{ $vlan->domainData->domain ?? '' }}
                    </option>
                  @endforeach
                </select>
                <input type="hidden" name="vlanid" id="vlanid_{{ $item->id }}" value="{{ $item->vlanid ?? '' }}">
              </div>

              <div class="mb-3">
                        <label for="service" class="form-label">Service</label>
                         <select class="form-select"  name="service" id="service" aria-label="Default select example">

                          <option selected="">Select Service</option>
                         
                            @foreach ($services as $service)
                <option value="{{ $service->id }}" {{ $item->service == $service->id ? 'selected' : '' }}>
                  {{ $service->service }}
                </option>
              @endforeach
                        </select>
                    </div>
         

          <div class="mb-3">
            <label class="form-label">Device</label>
            <input type="text" name="device" class="form-control" value="{{ $item->device }}" >
          </div>

         
          <div class="mb-3">
            <label class="form-label">IP</label>
            <input type="text" name="ip" class="form-control" value="{{ $item->ip }}" >
          </div>
          <div class="mb-3">
            <label class="form-label">Rack</label>
            <input type="text" name="rack" class="form-control" value="{{ $item->rack }}" >
          </div>
          <div class="mb-3">
            <label class="form-label">Bandwith</label>
            <input type="text" name="bandwith" class="form-control" value="{{ $item->bandwith ?? '' }}" >
          </div>
          <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="{{ $item->location }}" >
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>


                    <!-- Modal Delete Service (optional, jika ingin dinamis juga) -->
                    <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('ip.destroy', $item->id) }}" method="POST">
                                @csrf
                                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="domain" class="form-label">Remark</label>
                            <input type="text" name="remark" id="remark" class="form-control" placeholder="remark" required>
                        </div>
                     
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Delete</button>
                    </div>
                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
            <div class="col d-flex justify-content-end">
        {{ $ips->links('pagination::bootstrap-5') }}
    </div>
        </div>
    </div>
    <!--/ Basic Bootstrap Table -->

    <!-- Modal Add Service -->
    <div class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('ip.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">New IP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                         <div class="mb-3">
                         <label for="vlan" class="form-label">Vlan (VlanId - Name)</label>
                            <select class="form-select" name="vlan" id="create_vlan" aria-label="Default select example">
                              <option value="">Select Vlan</option>
                              @foreach($vlans as $vlan)
                                <option value="{{ $vlan->id }}" data-vlanid="{{ $vlan->vlanid ?? '' }}" data-domain="{{ $vlan->domainData->domain ?? '' }}">{{ ($vlan->vlanid ?? '') }} - {{ $vlan->vlan }} - {{ $vlan->domainData->domain ?? '' }}</option>
                              @endforeach
                            </select>
                            <input type="hidden" name="vlanid" id="create_vlanid" value="">
                        </div>
                         <div class="mb-3">
                        <label for="service" class="form-label">IP Sevices</label>
                         <select class="form-select"  name="service" id="service" aria-label="Default select example">
                          <option selected="">Select service</option>
                         
                            @foreach($services as $service)
                                <option value="{{ $service->id}}">{{ $service->service }}</option>
                            @endforeach
                        </select>
                    </div>
                        <div class="mb-3">
                            <label for="device" class="form-label">Device</label>
                            <input type="text" name="device" id="device" class="form-control" placeholder="device name" required>
                        </div>
                        <div class="mb-3">
                            <label for="ip" class="form-label">IP</label>
                            <input type="text" name="ip" id="ip" class="form-control" placeholder="Ip name" required>
                        </div>
                        <div class="mb-3">
                            <label for="rack" class="form-label">Rack</label>
                            <input type="text" name="rack" id="rack" class="form-control" placeholder="Rack name" >
                        </div>
                        <div class="mb-3">
                            <label for="bandwith" class="form-label">Bandwith</label>
                            <input type="text" name="bandwith" id="bandwith" class="form-control" placeholder="Bandwith" >
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" id="location" class="form-control" placeholder="Location" >
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Create modal select -> set hidden vlanid
  const createSelect = document.getElementById('create_vlan');
  const createHidden = document.getElementById('create_vlanid');
  if (createSelect) {
    createSelect.addEventListener('change', function () {
      const opt = this.options[this.selectedIndex];
      createHidden.value = opt ? opt.dataset.vlanid || '' : '';
    });
  }

  // For edit selects (multiple in page)
  document.querySelectorAll('.edit-vlan').forEach(function(sel){
    const id = sel.dataset.item;
    const hidden = document.getElementById('vlanid_' + id);
    // set initial value from selected option (in case option selected server-side)
    const initOpt = sel.options[sel.selectedIndex];
    if (hidden && initOpt) hidden.value = initOpt.dataset.vlanid || hidden.value || '';

    sel.addEventListener('change', function () {
      const opt = this.options[this.selectedIndex];
      if (hidden) hidden.value = opt ? opt.dataset.vlanid || '' : '';
    });
  });
});
</script>
@endsection