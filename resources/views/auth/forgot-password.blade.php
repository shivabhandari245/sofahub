<x-guest-layout>
    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo">
            <!-- Replace with your application logo -->
            <svg width="65" height="65" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z"
                    fill="#2e2e2e" />
            </svg>
        </div>

        <h2 class="login-title">Reset Your Password</h2>

        <!-- Instructions -->
        <div class="login-instructions mb-4">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        <!-- Session Status -->
        @if (session('status'))
        <div class="login-status-message login-status-success">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="login-form-group">
                <label for="email" class="login-label">Email Address</label>
                <input id="email" class="login-input @error('email') login-input-error @enderror" type="email"
                    name="email" value="{{ old('email') }}" required autofocus autocomplete="email">

                @error('email')
                <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-4">
                <a class="login-forgot-password" href="{{ route('login') }}">
                    Back to Login
                </a>

                <button type="submit" class="login-btn">
                    Email Password Reset Link
                </button>
            </div>
        </form>

        <!-- Footer -->
        <div class="login-footer-text">
            Remember your password?
            <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>

    <style>
    /* Background */
    .min-h-screen {
        background: linear-gradient(135deg, #f4f6f9, #e9ecf1) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* Card */
    .login-container {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border-radius: 14px;
        padding: 40px 35px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    /* Logo */
    .login-logo {
        margin-bottom: 15px;
    }

    .login-logo svg {
        width: 60px;
        height: 60px;
    }

    /* Title */
    .login-title {
        font-size: 1.6rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
    }

    /* Instructions */
    .login-instructions {
        font-size: 0.95rem;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    /* Status message */
    .login-status-message {
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 20px;
    }

    .login-status-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    /* Form Group */
    .login-form-group {
        text-align: left;
        margin-bottom: 22px;
    }

    /* Label */
    .login-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
        display: block;
    }

    /* Input */
    .login-input {
        width: 100%;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .login-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        outline: none;
    }

    /* Error */
    .login-input-error {
        border-color: #ef4444;
    }

    .login-error-message {
        margin-top: 6px;
        font-size: 0.8rem;
        color: #dc2626;
    }

    /* Actions row */
    .flex.items-center.justify-between {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 25px;
    }

    /* Links */
    .login-forgot-password {
        font-size: 0.85rem;
        color: #6366f1;
        text-decoration: none;
        transition: color 0.2s;
    }

    .login-forgot-password:hover {
        color: #4338ca;
        text-decoration: underline;
    }

    /* Button */
    .login-btn {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        border: none;
        padding: 11px 22px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .login-btn:hover {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
    }

    /* Footer */
    .login-footer-text {
        margin-top: 30px;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .login-footer-text a {
        color: #6366f1;
        text-decoration: none;
        font-weight: 500;
    }

    .login-footer-text a:hover {
        text-decoration: underline;
    }

    /* Mobile */
    @media (max-width: 480px) {
        .login-container {
            padding: 30px 25px;
        }

        .login-title {
            font-size: 1.4rem;
        }
    }
    </style>

</x-guest-layout>