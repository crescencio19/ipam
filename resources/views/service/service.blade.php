@extends('layout.main')

@section('content')
<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
 <form action="{{ route('service.service') }}" method="GET" class="navbar-nav align-items-center">
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
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Location</th>
                        <th>Longlat</th>

                        <th>Description</th>
                        {{-- <th>Actions</th> --}}
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($services as $item) 
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ optional($item->domainData)->domain ?? '-' }}</td>
                        <td>{{ optional($item->servicesData)->service ?? $item->service ?? '-' }}</td>

                        <td>{{$item->customer}}</td>
                        <td>{{$item->location}}</td>
                        <td>{{$item->longlat}}</td>
                        <td>{{$item->description}}</td>
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
                            <form action="{{ route('service.update', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="domain_{{ $item->id }}" class="form-label">Domain</label>
                                            <select class="form-select edit-domain-select" name="domain" id="domain_{{ $item->id }}" required>
                                                <option value="">Select domain</option>
                                                @foreach($domains as $domain)
                                                  <option value="{{ $domain->id }}" {{ $item->domain == $domain->id ? 'selected' : '' }}>
                                                    {{ $domain->domain }}
                                                  </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="service_{{ $item->id }}" class="form-label">Service</label>
                                            <select name="service" id="service_{{ $item->id }}" class="form-select edit-service-select" required>
                                              <option value="{{ $item->service }}" selected>{{ $item->service }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer{{ $item->id }}" class="form-label">Customer</label>
                                            <input type="text" name="customer" id="customer{{ $item->id }}" class="form-control" value="{{ $item->customer }}" >
                                        </div>
                                        <div class="mb-3">
                                            <label for="location{{ $item->id }}" class="form-label">Location</label>
                                            <input type="text" name="location" id="location{{ $item->id }}" class="form-control" value="{{ $item->location }}" >
                                        </div>
                                        <div class="mb-3">
                                            <label for="longlat{{ $item->id }}" class="form-label">Longlat</label>
                                            <input type="text" name="longlat" id="longlat{{ $item->id }}" class="form-control" value="{{ $item->longlat }}" >
                                        </div>
                                        <div class="mb-3">
                                            <label for="description{{ $item->id }}" class="form-label">Description</label>
                                            <input type="text" name="description" id="description{{ $item->id }}" class="form-control" value="{{ $item->description }}" required>
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
                            <form action="{{ route('service.destroy', $item->id) }}" method="POST">
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
            <div class="mt-3 d-flex justify-content-end">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    <!--/ Basic Bootstrap Table -->

    <!-- Modal Add Service -->
    <div class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('service.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">New Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                          <label for="create_domain" class="form-label">Domain</label>
                          <select class="form-select create-domain-select" name="domain" id="create_domain" required>
                            <option value="">Select Domain</option>
                            @foreach($domains as $domain)
                              <option value="{{ $domain->id }}">{{ $domain->domain }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="mb-3">
                          <label for="create_service" class="form-label">Service</label>
                          <select name="service" id="create_service" class="form-select create-service-select" required>
                            <option value="">Select Service</option>
                          </select>
                        </div>

                        <div class="mb-3">
                            <label for="customer" class="form-label">Customer</label>
                            <input type="text" name="customer" id="customer" class="form-control" placeholder="customer name" >
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" id="location" class="form-control" placeholder="location name" >
                        </div>
                        <div class="mb-3">
                            <label for="longlat" class="form-label">Longlat</label>
                            <input type="text" name="longlat" id="longlat" class="form-control" placeholder="longlat name" >
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control" placeholder="service description" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
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

  // init Select2 for Create modal (dropdownParent must be modal)
  $('.create-domain-select').select2({
    width: '100%',
    placeholder: 'Select domain',
    allowClear: true,
    dropdownParent: $('#basicModal')
  });
  $('.create-service-select').select2({
    width: '100%',
    placeholder: 'Select service',
    allowClear: true,
    dropdownParent: $('#basicModal')
  });

  // init Edit selects per-modal (dropdownParent = that modal)
  $('.edit-domain-select').each(function () {
    const $dom = $(this);
    const $modal = $dom.closest('.modal');
    $dom.select2({ width: '100%', placeholder: 'Select domain', allowClear: true, dropdownParent: $modal });
  });
  $('.edit-service-select').each(function () {
    const $svc = $(this);
    const $modal = $svc.closest('.modal');
    $svc.select2({ width: '100%', placeholder: 'Select service', allowClear: true, dropdownParent: $modal });
  });

  function loadServicesForDomain(domainId, $serviceSelect, selectedValue = null) {
    console.log('loadServicesForDomain', domainId);
    $serviceSelect.prop('disabled', true).empty().append($('<option>', { value: '', text: 'Loading...' })).trigger('change.select2');

    if (!domainId) {
      $serviceSelect.empty().append($('<option>', { value: '', text: 'Select Service' })).trigger('change.select2').prop('disabled', false);
      return;
    }

    $.getJSON("{{ route('service.byDomain') }}", { domain: domainId })
      .done(function (data) {
        console.log('byDomain response:', data);
        $serviceSelect.empty().append($('<option>', { value: '', text: 'Select Service' }));
        if (Array.isArray(data) && data.length) {
          data.forEach(function(row){
  $serviceSelect.append($('<option>', { value: row.id, text: row.service }));
});
        } else {
          $serviceSelect.append($('<option>', { value: '', text: 'No services for this domain' }));
        }
        if (selectedValue) $serviceSelect.val(selectedValue);
        $serviceSelect.trigger('change.select2');
      })
      .fail(function (xhr, status, err) {
        console.error('Failed to load services:', status, err, xhr.responseText);
        $serviceSelect.empty().append($('<option>', { value: '', text: 'Error loading' })).trigger('change.select2');
      })
      .always(function () {
        $serviceSelect.prop('disabled', false);
      });
  }

  // Create modal domain -> load services
  $('#create_domain').on('change', function () {
    loadServicesForDomain($(this).val(), $('#create_service'));
  });

  // Edit modals: bind and preload when modal opens
  $('.edit-domain-select').each(function () {
    const $dom = $(this);
    const id = $dom.attr('id').replace('domain_','');
    const $svc = $('#service_' + id);

    $dom.on('change', function () {
      loadServicesForDomain($dom.val(), $svc, null);
    });

    $dom.closest('.modal').on('shown.bs.modal', function () {
      const domainId = $dom.val();
      const current = $svc.find('option:selected').val();
      if (domainId) loadServicesForDomain(domainId, $svc, current);
    });
  });
});
</script>
@endpush