@extends('layouts.admin')

@section('title', 'Profile - SofaHub Admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/profile.css') }}" />
@endpush
@section('content')
    <div class="card">
        <h2>Profile Information</h2>
        <p>Update your account's profile information and email address.</p>
    </div>
    <div class="settings-grid">
        <!-- Profile Information -->
        <div class="settings-card">
            <h3><i class="bi bi-person-circle"></i> Profile Information</h3>
            
            <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('patch')

                <div class="form-group">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn btn-primary">Save</button>

                    @if (session('status') === 'profile-updated')
                        <p class="text-success">Profile updated successfully!</p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div class="settings-card">
            <h3><i class="bi bi-shield-lock"></i> Update Password</h3>
            
            <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('put')

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
                    @error('current_password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" />
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
                    @error('password_confirmation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn btn-primary">Update Password</button>

                    @if (session('status') === 'password-updated')
                        <p class="text-success">Password updated successfully!</p>
                    @endif
                </div>
            </form>
        </div>

<!-- Admin Profile (Static Content) -->
<div class="settings-card">
    <h3><i class="bi bi-person-badge"></i> Admin Profile</h3>

    <p class="text-muted">
        This panel displays the static profile information of the system administrator.
        The admin is responsible for managing users, monitoring system activity, maintaining
        data integrity, and ensuring smooth operation of all system modules.
    </p>

    <div class="mt-3">
        <p><strong>Role:</strong> System Administrator</p>
        <p><strong>Access Level:</strong> Full Access</p>
        <p><strong>Responsibilities:</strong></p>
        <ul>
            <li>User and role management</li>
            <li>Raw material and stock monitoring</li>
            <li>Production and dispatch control</li>
            <li>Sales and invoice oversight</li>
            <li>System configuration and maintenance</li>
        </ul>
    </div>
</div>

    </div>

@endsection