@extends('admin.layouts.app')
@section('title', 'Users')

@section('css')

@endsection

@section('users.index','active')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <div class="d-md-flex d-sm-block">
                    <div class="form-group d-flex mb-0">
                        <h5>Users</h5>
                    </div>
                    <div class="flex-grow-1 text-end">
                        <form action="#!" method="GET">
                            <div class="btn-group">
                                <input class="form-control" type="text" name="search" placeholder="Search"
                                    style="boarder:1px solid black" value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="table-responsive table-hover">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            {{-- <th>Port</th> --}}
                            <th>Last Login</th>
                            <th>Number of Queries</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            {{-- <td>{{ $user->port }}</td> --}}
                            <td>
                                @if ($user->last_login == null)
                                    <span class="badge badge-danger">N/A</span>
                                @else
                                    {{ \Carbon\Carbon::parse($user->last_login)->diffForHumans() }}
                                @endif
                            </td>
                            <td>{{ $user->total_search }}</td>
                            <td>
                                @if ($user->status == 1)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userEdit{{ $user->id }}">
                                        Edit
                                    </button>
                                    @if ($user->status == 1)
                                        <a href="{{ route('users.status', $user->id) }}" class="btn btn-sm btn-warning">Deactivate</a>
                                    @else
                                        <a href="{{ route('users.status', $user->id) }}"  class="btn btn-sm btn-info">Activate</a>
                                    @endif
                                    <button class="btn btn-sm btn-danger sweet-6" data-id="{{ $user->id }}" type="button">Delete</button>
                                </div>
                            </td>

                            <div class="modal fade" id="userEdit{{ $user->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="userEdit{{ $user->id }}Label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="userEdit{{ $user->id }}Label">Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('users.update', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                                </div>

                                                {{-- <div class="form-group">
                                                    <label for="port">Port</label>
                                                    <input type="number" name="port" id="port" class="form-control" value="{{ old('port', $user->port) }}" required>
                                                </div> --}}
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body">
            {{ $users->render() }}
        </div>
    </div>
</div>
@endsection


@section('js')
<script>
    $(document).ready(function () {
        $('.sweet-6').click(function (e) {
            e.preventDefault();
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    var success = true;
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: "delete",
                        url: "{{ route('users.delete',':id') }}".replace(':id', $(this)
                            .attr('data-id')),
                        dataType: "dataType",

                        statusCode: {
                            202: function (response) {
                                // Handle the 202 status code as success
                                swalWithBootstrapButtons.fire({

                                    title: 'Accepted!',
                                    text: JSON.parse(response.responseText)
                                        .success ||
                                        'The request has been accepted for processing.',
                                    icon: 'success',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,

                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            },
                        },

                        error: function (response) {

                            swalWithBootstrapButtons.fire(
                                'Error!',
                                JSON.parse(response.responseText).error ||
                                'Something went wrong!',
                                'error'
                            );
                        }
                    });
                }
            })

        });
    });

</script>

@endsection
