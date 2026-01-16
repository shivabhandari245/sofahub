@extends('layouts.admin')

@section('title', 'User Accounts Management')
@section('page-title', 'User Accounts Overview')

@section('content')

{{-- ================= ALERTS ================= --}}
@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error">
    {{ session('error') }}
</div>
@endif

{{-- ================= HEADER ================= --}}
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>User Accounts</h3>
        <button type="button" id="toggleFormBtn" class="btn btn-black">
            {{ isset($editUser) ? 'Close Form' : 'Add New User' }}
        </button>
    </div>
</div>

{{-- ================= ADD / EDIT USER ================= --}}
@if(isset($editUser))
<div class="card" id="userFormCard">
    @else
    <div class="card" id="userFormCard" style="display:none;">
        @endif

        <h3>{{ isset($editUser) ? 'Edit User' : 'Create New User' }}</h3>

        <form method="POST"
            action="{{ isset($editUser) ? route('account.update', $editUser) : route('admin.account.add') }}">
            @csrf
            @isset($editUser)
            @method('PUT')
            @endisset

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
                <div>
                    <label>Username</label>
                    <input type="text" name="name" value="{{ old('name', $editUser->name ?? '') }}" required>
                </div>

                <div>
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $editUser->email ?? '') }}" required>
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" name="password" {{ isset($editUser) ? '' : 'required' }}
                        placeholder="{{ isset($editUser) ? 'Leave blank to keep password' : '' }}">
                </div>

                <div>
                    <label>Roles</label>
                    <select name="role[]" multiple required>
                        @foreach($roles as $role)
                        <option value="{{ $role }}"
                            {{ isset($editUser) && $editUser->hasRole($role) ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">
                    Update User
                </button>
            </div>

        </form>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="card">
        <form method="GET" action="{{ route('accounts') }}"
            style="display:grid; grid-template-columns:1fr 200px auto; gap:12px;">
            <input type="text" name="search" placeholder="Search by name or email" value="{{ request('search') }}">

            <select name="role">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                <option value="{{ $role }}" @selected(request('role')==$role)>
                    {{ $role }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-black">Filter</button>
        </form>
    </div>

    {{-- ================= APPROVED USERS ================= --}}
    <div class="card">
        <h3>Approved Users</h3>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td data-label="ID">{{ $user->id }}</td>
                    <td data-label="User">{{ $user->name }}</td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Roles">
                        @foreach($user->roles as $role)
                        <span class="role-badge">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td data-label="Actions">
                        <a href="{{ route('accounts', ['edit_id' => $user->id]) }}" class="btn btn-black">
                            Edit
                        </a>

                        <form action="{{ route('account.delete', $user) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this user?')">
                                Delete
                            </button>
                        </form>
                    </td>
                 <td data-label="Impersonate">
    @if(!$user->hasRole('admin'))
    <form action="{{ route('admin.impersonate', $user->id) }}"
          method="POST"
          onsubmit="return confirm('Login as this user?')">
        @csrf
        <button class="btn btn-sm btn-warning">
            Login as User
        </button>
    </form>
    @else
        <span class="text-muted">—</span>
    @endif
</td>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

  <div class="card">
    <h3>Pending User Approvals</h3>

    @if($pendingUsers->isEmpty())
        <p class="text-muted">No pending users.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th width="140">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingUsers as $user)
                <tr>
                    <td data-label="Name">{{ $user->name }}</td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Action">
                        <form action="{{ route('users.approve', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Approve this user?')">
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

    @endsection

    @push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('toggleFormBtn');
    const form = document.getElementById('userFormCard');

    if (!btn || !form) return; // Prevent "Cannot read properties of null"

    btn.addEventListener('click', () => {
        const isHidden = form.style.display === 'none' || form.style.display === '';
        form.style.display = isHidden ? 'block' : 'none';
        btn.textContent = isHidden ? 'Close Form' : 'Add User';
    });
});
</script>

    @endpush