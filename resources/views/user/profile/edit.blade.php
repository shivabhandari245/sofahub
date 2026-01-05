@extends('layouts.user')

@section('title', 'Profile - SofaHub User')
@push('styles')
<link rel="stylesheet" href="{{asset('css/usercss/profile.css')}}">
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

        <!-- Delete Account -->
        <div class="settings-card">
            <h3><i class="bi bi-trash"></i> Delete Account</h3>
            <p class="text-muted">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
                Delete Account
            </button>

            <!-- Delete Account Modal -->
            <div class="modal fade" id="confirmUserDeletion" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete Account</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                            <form method="post" action="{{ route('profile.destroy') }}" id="deleteUserForm">
                                @csrf
                                @method('delete')
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Enter your password to confirm" required>
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" form="deleteUserForm" class="btn btn-danger">Delete Account</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection