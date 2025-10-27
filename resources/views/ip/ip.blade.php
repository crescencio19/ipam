@extends('layout.main')
@section('search-action', route('ip.ip'))

@section('content')
  {{-- Validation / error toast --}}
  @if ($errors->any() || session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1080;">
      <div id="errorToast" class="bs-toast toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
          <i class="bx bx-error me-2"></i>
          <div class="me-auto fw-semibold">Error</div>
          <small class="text-white-50">Now</small>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-white text-danger">
          @if(session('error'))
            {{ session('error') }}
          @else
            {{-- tampilkan pesan pertama --}}
            {{ $errors->first() }}
            {{-- jika mau semua pesan, uncomment berikut:
            <ul class="mb-0">
              @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
            --}}
          @endif
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('errorToast');
        if (el) {
          try {
            const t = new bootstrap.Toast(el, { autohide: true, delay: 5000 });
            t.show();
          } catch (e) { console.warn(e); }
        }
      });
    </script>
  @endif
  
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
                        <th>Device Customer</th>
                        <th>IP</th>
                        <th>IP Customer</th>
                        <th>VlanID</th>
                        <th>Vlan</th>
                        <th>Services</th>
                        <th>Rack</th>
                        <th>Bandwith</th>
                        <th>Location</th>
                        <th>R Number</th>
                        <th>B Number</th>
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
                      <td>{{ $loop->iteration }}</td>

                          <td>{{ $item->device ?? '-' }}</td>
                         <td>{{ $item->devicecs ?? '-' }}</td>
                         <td>{{ $item->ip ?? '-' }}</td>
                         <td>{{ $item->ipcs ?? '-' }}</td>
                          <td>{{ $item->vlanid_value ?? $item->vlanid ?? '-' }}</td>
                         <td>{{ $item->vlan_name ?? '-' }}</td>
                         <td>{{ $item->service_name ?? $item->service ?? '-' }}</td>
                         <td>{{ $item->rack ?? '-' }}</td>
                         <td>{{ $item->bandwith ?? '-' }}</td>
                         <td>{{ $item->location ?? '-' }}</td>
                         <td>{{ $item->r_number ?? '-' }}</td>
                         <td>{{ $item->b_number ?? '-' }}</td>

                         <td>
                            @if($hasServiceFinal)
                              <!-- locked: edit/delete disabled for rows with service -->
                              <button type="button" class="btn btn-secondary btn-sm" disabled title="Edit disabled for items with service">
                                <i class='bx bx-edit'></i>
                              </button>
                              <button type="button" class="btn btn-secondary btn-sm" disabled title="Delete disabled for items with service">
                                <i class='bx bx-trash'></i>
                              </button>
                            @else
                              <!-- Tombol Edit -->                        
                              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                <i class='bx bx-edit'></i>
                              </button>
                              <!-- Tombol Delete -->
                              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                <i class='bx bx-trash'></i>
                              </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Modal Edit Service (replace your existing edit modal block per item with this) -->
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form action="{{ route('ip.update', $item->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit IP - {{ $item->ip ?? $item->id }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">VLAN (VLANID - NAME)</label>
              <select class="form-select edit-vlan ip-vlan-select" name="vlan" id="vlan_{{ $item->id }}" data-item="{{ $item->id }}">
                <option value="">Select Vlan</option>
                @foreach ($vlans as $vlan)
                  <option value="{{ $vlan->id }}" data-domain="{{ $vlan->domain ?? '' }}" data-vlanid="{{ $vlan->vlanid ?? '' }}" {{ ($item->vlan == $vlan->id) ? 'selected' : '' }}>
                    {{ $vlan->vlanid ?? '' }} - {{ $vlan->vlan ?? '' }}
                  </option>
                @endforeach
              </select>
              <input type="hidden" name="vlanid" id="vlanid_{{ $item->id }}" value="{{ $item->vlanid ?? '' }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Services</label>
              <select class="form-select edit-service ip-service-select" name="service" id="service_{{ $item->id }}" data-selected="{{ $item->service ?? '' }}">
                <option value="">Select service</option>
                @foreach ($services as $s)
                  <option value="{{ $s->id }}" {{ (string)$item->service === (string)$s->id ? 'selected' : '' }}>
                    {{ $s->service }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Device</label>
              <select name="device" class="form-select edit-device-select ip-device-select" data-selected="{{ $item->device ?? '' }}">
                <option value="">{{ $item->device ?? 'Select device' }}</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Device CS</label>
              <select name="devicecs" class="form-select edit-devicecs-select" data-selected="{{ $item->devicecs ?? '' }}">
                <option value="">{{ $item->devicecs ?? 'Select devicecs' }}</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">IP</label>
              <input type="text" name="ip" class="form-control" value="{{ $item->ip ?? '' }}" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">IP CS</label>
              <input type="text" name="ipcs" class="form-control" value="{{ $item->ipcs ?? '' }}">
            </div>

            <div class="col-md-6">
              <label for="rack" class="form-label">Rack</label>
              <select name="rack" class="form-select edit-rack-select" data-selected="{{ $item->rack ?? '' }}">
                <option value="">{{ $item->rack ?? 'Select rack' }}</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Bandwith</label>
              <input type="text" name="bandwith" class="form-control" value="{{ $item->bandwith ?? '' }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Location</label>
              <input type="text" name="location" class="form-control" value="{{ $item->location ?? '' }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">R Number</label>
              <input type="text" name="r_number" class="form-control" value="{{ $item->r_number ?? '' }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">B Number</label>
              <input type="text" name="b_number" class="form-control" value="{{ $item->b_number ?? '' }}">
            </div>
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
        <div class="modal-dialog modal-xl">
            <form action="{{ route('ip.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">New IP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label for="vlan" class="form-label">Vlan (VlanId - Name)</label>
                          <select class="form-select ip-vlan-select" name="vlan" id="create_vlan">
                            <option value="">Select Vlan</option>
                            @foreach($vlans as $vlan)
                              <option value="{{ $vlan->id }}" data-domain="{{ $vlan->domain }}" data-vlanid="{{ $vlan->vlanid }}">
                                {{ $vlan->vlanid }} - {{ $vlan->vlan }} @if(isset($vlan->domainData)) - {{ optional($vlan->domainData)->domain }} @endif
                              </option>
                            @endforeach
                          </select>
                          <input type="hidden" name="vlanid" id="create_vlanid" value="">
                        </div>

                        <div class="col-md-6">
                          <label for="create_service" class="form-label">Services</label>
                          <select class="form-select ip-service-select"  name="service" id="create_service">
                            <option value="">Select service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id}}">{{ $service->service }}</option>
                            @endforeach
                          </select>
                        </div>
 <div class="col-md-6">
                          <label class="form-label">Device</label>
                          <select class="form-select create-device-select ip-device-select" name="device" id="create_device">
                            <option value="">Select device</option>
                          </select>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Device CS</label>
                          <select class="form-select create-devicecs-select" name="devicecs" id="create_devicecs">
                            <option value="">Select devicecs</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label for="ip" class="form-label">IP</label>
                          <input type="text" name="ip" id="create_ip" class="form-control" required>
                        </div>
                         <div class="col-md-6">
                          <label class="form-label">IP CS</label>
                          <input type="text" name="ipcs" class="form-control" id="create_ipcs" value="">
                        </div>


                        <div class="col-md-6">
                          <label for="rack" class="form-label">Rack</label>
                          <select class="form-select create-rack-select rack-select" name="rack" id="create_rack">
                            <option value="">Select rack</option>
                          </select>
                        </div>

                        <div class="col-md-6">
                          <label for="bandwith" class="form-label">Bandwith</label>
                          <input type="text" name="bandwith" id="bandwith" class="form-control">
                        </div>

                        <div class="col-md-6">
                          <label for="location" class="form-label">Location</label>
                          <input type="text" name="location" id="location" class="form-control">
                        </div>

                       

                       
                        <div class="col-md-6">
                          <label class="form-label">R Number</label>
                          <input type="text" name="r_number" class="form-control" id="create_r_number" value="">
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">B Number</label>
                          <input type="text" name="b_number" class="form-control" id="create_b_number" value="">
                        </div>
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
@push('styles')
<style>
/* make modal wider and use flex layout if needed */
.modal-dialog.modal-xl { max-width: 1100px; }
.modal-dialog.modal-xl .modal-content { display: flex; flex-direction: column; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // init select2 for create modal selects (if select2 tersedia)
  if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
    $('#create_vlan').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select VLAN', allowClear: true });
    $('#create_service').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select service', allowClear: true });
    $('#create_device').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select device', allowClear: true });
    $('#create_devicecs').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select devicecs', allowClear: true });
    $('#create_rack').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select rack', allowClear: true });
  }

  // init edit modal select2 instances
  $('.edit-device-select, .edit-devicecs-select, .edit-rack-select').each(function(){
    const $el = $(this);
    const $modal = $el.closest('.modal');
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
      $el.select2({ width: '100%', dropdownParent: $modal, placeholder: 'Select', allowClear: true });
    }
  });

  function loadServicesForDomain(domainId, $serviceSelect, selectedValue = null) {
    if (!$serviceSelect || !$serviceSelect.length) return;
    $serviceSelect.prop('disabled', true).empty().append($('<option>',{ value:'', text: 'Loading...' }));
    if (!domainId) {
      $serviceSelect.empty().append($('<option>',{ value:'', text: 'Select' })).trigger('change.select2').prop('disabled', false);
      return;
    }
    $.getJSON("{{ route('service.byDomain') }}", { domain: domainId })
      .done(function(data){
        $serviceSelect.empty().append($('<option>',{ value:'', text: 'Select' }));
        if (Array.isArray(data) && data.length) {
          data.forEach(function(row){
            $serviceSelect.append($('<option>', { value: row.id, text: row.service }));
          });
        } else {
          $serviceSelect.append($('<option>', { value:'', text: 'No services for this domain' }));
        }
        if (selectedValue) $serviceSelect.val(selectedValue);
        $serviceSelect.trigger('change.select2');
      })
      .fail(function(){
        $serviceSelect.empty().append($('<option>',{ value:'', text:'Error loading' })).trigger('change.select2');
      })
      .always(function(){ $serviceSelect.prop('disabled', false); });
  }

  function loadDevicesAndRackForDomain(domainId, $root) {
    if (!$root) $root = $(document);
    if (!domainId) {
      $root.find('.ip-device-select, .create-device-select, .edit-device-select').each(function(){
        $(this).empty().append($('<option>',{value:'', text:'Select device'})).trigger('change');
      });
      $root.find('.create-devicecs-select, .edit-devicecs-select').each(function(){
        $(this).empty().append($('<option>',{value:'', text:'Select devicecs'})).trigger('change');
      });
      $root.find('.create-rack-select, .edit-rack-select').each(function(){
        $(this).empty().append($('<option>',{value:'', text:'Select rack'})).trigger('change');
      });
      return;
    }

    // devices
    $.getJSON("{{ route('device.byDomain') }}", { domain: domainId })
      .done(function(data){
        $root.find('.ip-device-select, .create-device-select, .edit-device-select').each(function(){
          const $sel = $(this);
          const selected = $sel.data('selected') || $sel.attr('data-selected') || '';
          $sel.empty().append($('<option>',{value:'', text:'Select device'}));
          if (Array.isArray(data)) {
            data.forEach(function(r){
              const opt = $('<option>',{ value: r.device, text: r.device });
              if (selected && String(r.device) === String(selected)) opt.prop('selected', true);
              $sel.append(opt);
            });
          }
          if (selected) $sel.val(selected).trigger('change');
          else $sel.trigger('change');
        });

        $root.find('.create-devicecs-select, .edit-devicecs-select').each(function(){
          const $sel = $(this);
          const selected = $sel.data('selected') || $sel.attr('data-selected') || '';
          $sel.empty().append($('<option>',{value:'', text:'Select devicecs'}));
          if (Array.isArray(data)) {
            data.forEach(function(r){
              const opt = $('<option>',{ value: r.device, text: r.device });
              if (selected && String(r.device) === String(selected)) opt.prop('selected', true);
              $sel.append(opt);
            });
          }
          if (selected) $sel.val(selected).trigger('change');
          else $sel.trigger('change');
        });
      }).fail(function(){ console.warn('device.by-domain missing'); });

    // racks
    $.getJSON("{{ route('rack.byDomain') }}", { domain: domainId })
      .done(function(data){
        $root.find('.create-rack-select, .edit-rack-select').each(function(){
          const $sel = $(this);
          const selected = $sel.data('selected') || $sel.attr('data-selected') || '';
          $sel.empty().append($('<option>',{value:'', text:'Select rack'}));
          if (Array.isArray(data)) {
            data.forEach(function(rr){
              const opt = $('<option>',{ value: rr.rack, text: rr.rack });
              if (selected && String(rr.rack) === String(selected)) opt.prop('selected', true);
              $sel.append(opt);
            });
          }
          if (selected) $sel.val(selected).trigger('change');
          else $sel.trigger('change');
        });
      }).fail(function(){ console.warn('rack.by-domain missing'); });
  }

  // create modal handlers
  $('#create_vlan').on('change', function () {
    const domain = $(this).find(':selected').data('domain') || '';
    loadServicesForDomain(domain, $('#create_service'));
    loadDevicesAndRackForDomain(domain, $('#basicModal'));
    $('#create_vlanid').val($(this).find(':selected').data('vlanid') || '');
  });

  // generic edit select handler (per-modal)
  $(document).on('change', '.ip-vlan-select', function () {
    const $sel = $(this);
    const $modal = $sel.closest('.modal');
    const domain = $sel.find(':selected').data('domain') || '';
    const $serviceSelect = $modal.find('.ip-service-select');
    loadServicesForDomain(domain, $serviceSelect);
    loadDevicesAndRackForDomain(domain, $modal);
    const id = $sel.data('item') || $sel.attr('id')?.replace('vlan_','');
    if (id) { $('#vlanid_' + id).val($sel.find(':selected').data('vlanid') || ''); }
  });

  // when edit modal opens, preload services/devices/racks
  $(document).on('shown.bs.modal', '.modal', function () {
    const $modal = $(this);
    const $vsel = $modal.find('.ip-vlan-select');
    const $s = $modal.find('.ip-service-select');
    if ($vsel.length && $s.length) {
      const domain = $vsel.find(':selected').data('domain') || '';
      loadServicesForDomain(domain, $s, $s.data('selected') || null);
      loadDevicesAndRackForDomain(domain, $modal);
    }
  });

});
</script>
@endpush

@endsection