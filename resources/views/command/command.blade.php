@extends('layout.main')

@section('content')
<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
 <form action="{{ route('command.command') }}" method="GET" class="navbar-nav align-items-center">
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
                        <th>Ip</th>
                        <th>Function</th>
                        <th>Command</th>
                        <th>Description</th>
                        
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($commands as $item) 
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                       
                      <td>{{ $item->ipData->ip ?? 'N/A' }} - {{ $item->ipData->device ?? 'N/A' }}</td>
                        <td>{{ $item->service_command }}</td>
                        <td>{{ $item->command }}</td>
                        <td>{{ $item->description }}</td>

                        
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
    <form action="{{ route('command.update', $item->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit VLAN</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
              <div class="mb-3">
                        <label for="ip" class="form-label">IP</label>
                         <select class="form-select ip-select" name="ip" id="ip_edit_{{ $item->id }}" aria-label="Default select example" style="width:100%">
                          <option value="">Select IP</option>
                         
                            @foreach ($ips as $ip)
                <option value="{{ $ip->id }}" {{ $item->ip == $ip->id ? 'selected' : '' }}>
                  {{ $ip->ip }} - {{ $ip->device }}
                </option>
              @endforeach
                        </select>
                    </div>
         

          <div class="mb-3">
            <label class="form-label">Service Command </label>
            <input type="text" name="service_command" class="form-control" value="{{ $item->service_command }}" required>
          </div>
            <div>
          <label for="exampleFormControlTextarea1" class="form-label">Command</label>
                        <textarea class="form-control" name="command" id="exampleFormControlTextarea1" required rows="3" style="height: 98px;">{{ $item->command }}</textarea>

                      </div>
          
          {{-- <div class="mb-3">
            <label class="form-label">Command</label>
            <input type="text" name="command" class="form-control" value="{{ $item->command }}" required>
          </div> --}}

          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" value="{{ $item->description }}" required>
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
                            <form action="{{ route('command.destroy', $item->id) }}" method="POST">
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
        {{ $commands->links('pagination::bootstrap-5') }}
    </div>

        </div>
    </div>
    <!--/ Basic Bootstrap Table -->

    <!-- Modal Add Service -->
    <div class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('command.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">New Service Command</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   
                    <div class="modal-body">
                        <div class="mb-3">
      <label for="ip" class="form-label">Ip</label>
      <select class="form-select ip-select" name="ip" id="ip" aria-label="Default select example" style="width:100%">
        <option value="">Select ip</option>
        @foreach($ips as $ip)
          <option value="{{ $ip->id }}">{{ $ip->ip }} - {{ $ip->device }}</option>
        @endforeach
      </select>
    </div>
                          <div class="mb-3">
                            <label for="service_command" class="form-label">Service Command</label>
                            <input type="text" name="service_command" id="service_command" class="form-control" placeholder="service command" required>
                        </div>

                        <div>
                        <label for="exampleFormControlTextarea1" class="form-label">Command</label>
                        <textarea class="form-control" name="command" id="exampleFormControlTextarea1" placeholder="command" required rows="3" style="height: 98px;"></textarea>
                        
                      </div>
                          
                             <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control" placeholder="description" required>
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* optional: buat search box terlihat rapi di dalam dropdown */
.select2-container--default .select2-search--dropdown .select2-search__field {
  box-shadow: none;
  border: 1px solid #e3e7ef;
  padding: .4rem .5rem;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // init for add modal
  $('#basicModal .ip-select').select2({
    width: '100%',
    placeholder: 'Search IP - device',
    allowClear: true,
    dropdownParent: $('#basicModal'),
    minimumResultsForSearch: 0 // 0 = always show search box
  });

  // init for each edit modal select (handles multiple modals)
  document.querySelectorAll('.ip-select[id^="ip_edit_"]').forEach(function(el){
    $(el).select2({
      width: '100%',
      placeholder: 'Search IP - device',
      allowClear: true,
      dropdownParent: $(el).closest('.modal'),
      minimumResultsForSearch: 0
    });
  });

  // if modals created dynamically, re-init on modal show
  $(document).on('shown.bs.modal', '.modal', function () {
    $(this).find('.ip-select').each(function () {
      if (!$(this).hasClass('select2-hidden-accessible')) {
        $(this).select2({
          width: '100%',
          placeholder: 'Search IP - device',
          allowClear: true,
          dropdownParent: $(this).closest('.modal'),
          minimumResultsForSearch: 0
        });
      }
    });
  });
});
</script>
@endpush