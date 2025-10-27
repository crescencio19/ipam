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
            <div id="vlans-table-container">
              <table class="table">
                 <thead>
                     <tr>
                         <th>No</th>
                         <th>Domain</th>
                         <th>Vlan Id</th>
                         <th>Vlan</th>
                         <th>Description</th>
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

                 <!-- Modal Edit -->
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('vlansser.update', $item->id) }}" method="POST">
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
              <select class="form-select"  name="domain" id="domain" aria-label="Default select example">
              <option selected="">Select Domain</option>
              @foreach ($domains as $domain)
              <option value="{{ $domain->id }}" {{ $item->domain == $domain->id ? 'selected' : '' }}>
              {{ $domain->domain }}
              </option>
              @endforeach
              </select>
              </div>
         

          <div class="mb-3">
            <label class="form-label">VLAN</label>
            <input type="text" name="vlan" class="form-control" value="{{ $item->vlan }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">VLAN ID</label>
            <input type="text" name="vlanid" class="form-control" value="{{ $item->vlanid }}" required>
          </div>

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
                            <form action="{{ route('vlansser.destroy', $item->id) }}" method="POST">
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
            </div>
    <div id="vlans-pagination" class="col d-flex justify-content-end">
        {{ $vlans->links('pagination::bootstrap-5') }}
    </div>
 
         </div>
     </div>
    <!--/ Basic Bootstrap Table -->

    <!-- Modal Add Service -->
    <div class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('vlansser.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="basicModalLabel">New Vlan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   
                    <div class="modal-body">
                        <div class="mb-3">
                        <label for="domain" class="form-label">Domain</label>
                         <select class="form-select"  name="domain" id="domain" aria-label="Default select example">
                          <option selected="">Select Domain</option>
                         
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id}}">{{ $domain->domain }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                            <label for="vlanid" class="form-label">Vlan Id</label>
                            <input type="text" name="vlanid" id="vlanid" class="form-control" placeholder="vlanId" required>
                    </div>
                    <div class="mb-3">
                            <label for="vlan" class="form-label">Vlan</label>
                            <input type="text" name="vlan" id="vlan" class="form-control" placeholder="vlan" required>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  const tableContainer = document.getElementById('vlans-table-container');
  const paginationContainer = document.getElementById('vlans-pagination');

  if (!paginationContainer) return;

  function ajaxLoad(url, push = true) {
    fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(resp => {
        if (!resp.ok) throw new Error('Network response was not ok');
        return resp.text();
      })
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTable = doc.getElementById('vlans-table-container');
        const newPagination = doc.getElementById('vlans-pagination');

        if (newTable && tableContainer) tableContainer.innerHTML = newTable.innerHTML;
        if (newPagination && paginationContainer) paginationContainer.innerHTML = newPagination.innerHTML;

        // re-bind history
        if (push) history.pushState({ url: url }, '', url);
        // scroll to top of table
        tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
      })
      .catch(err => {
        console.error('AJAX pagination error', err);
        // fallback: full navigation
        window.location = url;
      });
  }

  // delegate clicks on pagination links
  paginationContainer.addEventListener('click', function (e) {
    const a = e.target.closest('a');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href === '#') return;
    e.preventDefault();
    ajaxLoad(href, true);
  });

  // handle browser back/forward
  window.addEventListener('popstate', function (e) {
    const url = document.location.href;
    ajaxLoad(url, false);
  });
});
</script>
@endsection