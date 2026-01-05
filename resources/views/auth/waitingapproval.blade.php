@extends('layouts.app')

@section('title', 'Waiting for Approval')

@section('content')
<div class="container text-center mt-5">
    <h1>Your account is pending for approval</h1>
    <p>Thank you for signing up! An administrator will approve your account soon.</p>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-login mt-4">Back to Login</button>
</form>

</div>

@push('styles')
<style>
    .btn {
        display: inline-block;
        padding: 1rem 2rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        color: white;
        background: linear-gradient(45deg, #9b59b6, #8e44ad);
        margin-top: 20px;
    }

    .btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush
@endsection
