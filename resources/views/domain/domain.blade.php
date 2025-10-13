@extends('layout.main')

@section('content')
<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
 <form action="{{ route('domain.domain') }}" method="GET" class="navbar-nav align-items-center">
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
                        <th>Description</th>
                        {{-- <th>Actions</th> --}}
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($domains as $item)
                    <tr>
                        <td>{{ ($domains->currentPage() - 1) * $domains->perPage() + $loop->iteration }}</td>
                        <td>{{ $item->domain }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <form action="{{ route('domain.update', $item->id) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Edit Domain</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label">Domain</label>
                                <input type="text" name="domain" class="form-control" value="{{ $item->domain }}" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" value="{{ $item->description }}">
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

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <form action="{{ route('domain.destroy', $item->id) }}" method="POST">
                          @csrf
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Delete Domain</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label">Remark</label>
                                <input type="text" name="remark" class="form-control" placeholder="remark" required>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-danger">Delete</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>

                    @endforeach
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-end">
                {{ $domains->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('domain.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">New Domain</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Domain</label>
            <input type="text" name="domain" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control">
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

@endsection