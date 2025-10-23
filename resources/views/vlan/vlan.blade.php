@extends('layout.main')

@section('content')
<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
 <form action="{{ route('vlan.vlan') }}" method="GET" class="navbar-nav align-items-center">
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
                        <th>Domain</th>
                        <th>Vlan Id</th>
                        <th>Vlan</th>
                        <th>Gateway</th>
                        <th>Block IP</th>
                        {{-- <th>Actions</th> --}}
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($vlans as $item) 
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                       
                        <td>{{ $item->domainData->domain }}</td>

                         <td>{{$item->vlanid}}</td>
                         <td>{{$item->vlan}}</td>
                         <td>{{$item->gateway}}</td>
                        <td>{{$item->block_ip}}</td>
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

                 <!-- Modal Edit -->
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('vlan.update', $item->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit VLAN</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
              <div class="mb-3">
                        <label for="domain" class="form-label">Domain</label>
                         <select class="form-select edit-domain-select"  name="domain" id="domain_{{ $item->id }}" required>
                          <option value="">Select Domain</option>
                            @foreach ($domains as $domain)
                <option value="{{ $domain->id }}" {{ $item->domain == $domain->id ? 'selected' : '' }}>
                  {{ $domain->domain }}
                </option>
              @endforeach
                        </select>
                    </div>

          <div class="mb-3">
            <label class="form-label">VLAN (choose from domain)</label>
            <select class="form-select edit-vlan-select" name="vlansser_id" id="edit_vlansser_{{ $item->id }}" required>
              <option value="">Select VLAN</option>
              @if($item->vlanid && $item->vlan)
                {{-- try to set current value as an option so Select2 can show it --}}
                <option value="{{ $item->vlanid . '||' . $item->vlan }}" selected>{{ $item->vlanid }} — {{ $item->vlan }}</option>
              @endif
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Block IP</label>
            <input type="text" name="block_ip" class="form-control block-ip-input" value="{{ $item->block_ip }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Gateway</label>
            <input type="text" name="gateway" class="form-control gateway-input" value="{{ $item->gateway }}" required>
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
                            <form action="{{ route('vlan.destroy', $item->id) }}" method="POST">
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
        {{ $vlans->links('pagination::bootstrap-5') }}
    </div>

        </div>
    </div>
    <!--/ Basic Bootstrap Table -->

    <!-- Modal Add Service -->
    <div class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('vlan.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">New Vlan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   
                    <div class="modal-body">
                        <div class="mb-3">
                        <label for="domain" class="form-label">Domain</label>
                        <select class="form-select create-domain-select" name="domain" id="create_domain" required>
                            <option value="">Select Domain</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id}}">{{ $domain->domain }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="vlansser" class="form-label">VLAN (choose from domain)</label>
                        <select class="form-select create-vlan-select" name="vlansser_id" id="create_vlansser" required>
                            <option value="">Select VLAN</option>
                        </select>
                    </div>

                    <div class="mb-3">
                      <label for="block_ip" class="form-label">Block IP</label>
                      <input type="text" name="block_ip" id="block_ip" class="form-control block-ip-input" placeholder="block ip" required>
                    </div>
                    <div class="mb-3">
                      <label for="gateway" class="form-label">Gateway</label>
                      <input type="text" name="gateway" id="gateway" class="form-control gateway-input" placeholder="gateway " required>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
    console.warn('Select2 not loaded');
    return;
  }

  // Create modal selects
  $('#create_domain').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select domain', allowClear: true });
  $('#create_vlansser').select2({ width: '100%', dropdownParent: $('#basicModal'), placeholder: 'Select VLAN', allowClear: true });

  // Edit modal selects: initialize but parent is modal instance
  $('.edit-domain-select').each(function(){
    const $d = $(this);
    const $modal = $d.closest('.modal');
    $d.select2({ width: '100%', dropdownParent: $modal, placeholder: 'Select domain', allowClear: true });
  });
  $('.edit-vlan-select').each(function(){
    const $s = $(this);
    const $modal = $s.closest('.modal');
    $s.select2({ width: '100%', dropdownParent: $modal, placeholder: 'Select VLAN', allowClear: true });
  });

  // loader function
  function loadVlansForDomain(domainId, $vlanSelect, selectedId = null) {
    $vlanSelect.prop('disabled', true).empty().append($('<option>',{value:'', text: 'Loading...'})).trigger('change.select2');
    if (!domainId) {
      $vlanSelect.empty().append($('<option>',{value:'', text: 'Select VLAN'})).trigger('change.select2').prop('disabled', false);
      return;
    }
    $.getJSON("{{ route('vlansser.byDomain') }}", { domain: domainId })
      .done(function(data){
        $vlanSelect.empty().append($('<option>',{value:'', text: 'Select VLAN'}));
        data.forEach(function(row){
          $vlanSelect.append($('<option>', { value: row.id, text: row.text }));
        });
        if (selectedId) {
          // selectedId might be vlans_ser id or fallback string; try setting it
          $vlanSelect.val(selectedId).trigger('change.select2');
        }
      })
      .fail(function(){ 
        $vlanSelect.empty().append($('<option>',{value:'', text: 'Error loading'}));
      })
      .always(function(){ $vlanSelect.prop('disabled', false); });
  }

  // when create-domain changes -> load vlans for create_vlansser
  $('#create_domain').on('change', function(){
    loadVlansForDomain($(this).val(), $('#create_vlansser'));
  });

  // when edit-domain changes -> load for that modal's vlan select
  $('.edit-domain-select').on('change', function(){
    const $dom = $(this);
    const $modal = $dom.closest('.modal');
    const $vsel = $modal.find('.edit-vlan-select');
    loadVlansForDomain($dom.val(), $vsel);
  });

  // when edit modal opens, preload vlans for its current domain and try to select current value
  $('.modal').on('shown.bs.modal', function (e) {
    const $modal = $(this);
    const $dom = $modal.find('.edit-domain-select');
    const $vsel = $modal.find('.edit-vlan-select');
    if ($dom.length && $vsel.length) {
      const domainId = $dom.val();
      // try to set selected to existing vlansser id if present as option value
      const currentVal = $vsel.find('option:selected').val();
      loadVlansForDomain(domainId, $vsel, currentVal);
    }
  });
  // -- Gateway auto-generate from Block IP --
  function ipToInt(ip) {
    const parts = ip.split('.').map(Number);
    if (parts.length !== 4 || parts.some(isNaN)) return null;
    return ((parts[0] << 24) >>> 0) + ((parts[1] << 16) >>> 0) + ((parts[2] << 8) >>> 0) + (parts[3] >>> 0);
  }
  function intToIp(int) {
    return [(int >>> 24) & 0xFF, (int >>> 16) & 0xFF, (int >>> 8) & 0xFF, int & 0xFF].join('.');
  }
  function maskFromPrefix(prefix) {
    return prefix === 0 ? 0 : (~((1 << (32 - prefix)) - 1)) >>> 0;
  }
  function computeGatewayFromCidr(ipCidr) {
    // ipCidr like '192.0.2.0/24' or '192.0.2.0'
    if (!ipCidr) return null;
    ipCidr = ipCidr.trim();
    let ipPart = ipCidr;
    let prefix = 24; // default
    if (ipCidr.includes('/')) {
      const sp = ipCidr.split('/');
      ipPart = sp[0];
      prefix = parseInt(sp[1], 10);
      if (isNaN(prefix) || prefix < 0 || prefix > 32) prefix = 24;
    }
    const ipInt = ipToInt(ipPart);
    if (ipInt === null) return null;
    const mask = maskFromPrefix(prefix);
    const network = ipInt & mask;
    // choose first usable host (network + 1) except when prefix===32 then gateway is ip itself
    let gwInt = (prefix === 32) ? ipInt : (network + 1);
    // ensure gwInt is not broadcast for /31 or /32 edge cases
    return intToIp(gwInt >>> 0);
  }

  function attachAutoGateway(blockInput, gatewayInput) {
    if (!blockInput || !gatewayInput) return;
    const update = function() {
      const val = blockInput.value;
      const gw = computeGatewayFromCidr(val);
      if (gw) gatewayInput.value = gw;
    };
    // update on blur and on change
    blockInput.addEventListener('blur', update);
    blockInput.addEventListener('change', update);
    // also try on input (with debounce)
    let t;
    blockInput.addEventListener('input', function(){
      clearTimeout(t);
      t = setTimeout(update, 600);
    });
    // if gateway empty when modal opens, compute
    const modal = blockInput.closest('.modal');
    if (modal) {
      modal.addEventListener('shown.bs.modal', function(){
        // find inputs inside this modal
        const bi = modal.querySelector('.block-ip-input');
        const gi = modal.querySelector('.gateway-input');
        if (bi && gi && !gi.value) gi.value = computeGatewayFromCidr(bi.value);
      });
    } else {
      // for create (not modal) compute initial
      if (!gatewayInput.value) gatewayInput.value = computeGatewayFromCidr(blockInput.value);
    }
  }

  // attach to create inputs (ids)
  attachAutoGateway(document.getElementById('block_ip'), document.getElementById('gateway'));

  // attach to each edit modal pair
  document.querySelectorAll('.edit-vlan-select').forEach(function(){/* ensure selects initialized earlier */});
  document.querySelectorAll('.block-ip-input').forEach(function(bi){
    // find gateway input in same modal/row
    let container = bi.closest('.modal') || bi.closest('tr') || document;
    const gi = container.querySelector('.gateway-input');
    attachAutoGateway(bi, gi);
  });

});
</script>
@endpush