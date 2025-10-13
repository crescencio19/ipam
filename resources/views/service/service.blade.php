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
                        <td>{{$item->service}}</td>
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
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="service{{ $item->id }}" class="form-label">Service</label>
                                            <input type="text" name="service" id="service{{ $item->id }}" class="form-control" value="{{ $item->service }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer {{ $item->id }}" class="form-label">Customer</label>
                                            <input type="text" name="customer" id="customer {{ $item->id }}" class="form-control" value="{{ $item->customer }}" >
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
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>
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
                            <label for="service" class="form-label">Service</label>
                            <input type="text" name="service" id="service" class="form-control" placeholder="service name" required>
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