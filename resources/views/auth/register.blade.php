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

        <h2 class="login-title">Create Account</h2>

        <!-- Session Status -->
        @if (session('status'))
        <div class="login-status-message login-status-success">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="login-form-group">
                <label for="name" class="login-label">Full Name</label>
                <input id="name" class="login-input @error('name') login-input-error @enderror" type="text" name="name"
                    value="{{ old('name') }}" required autofocus autocomplete="name">

                @error('name')
                <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="login-form-group">
                <label for="email" class="login-label">Email Address</label>
                <input id="email" class="login-input @error('email') login-input-error @enderror" type="email"
                    name="email" value="{{ old('email') }}" required autocomplete="username">

                @error('email')
                <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="login-form-group">
                <label for="password" class="login-label">Password</label>
                <input id="password" class="login-input @error('password') login-input-error @enderror" type="password"
                    name="password" required autocomplete="new-password">

                @error('password')
                <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="login-form-group">
                <label for="password_confirmation" class="login-label">Confirm Password</label>
                <input id="password_confirmation"
                    class="login-input @error('password_confirmation') login-input-error @enderror" type="password"
                    name="password_confirmation" required autocomplete="new-password">

                @error('password_confirmation')
                <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-4">
                <a class="login-forgot-password" href="{{ route('login') }}">
                    Already registered?
                </a>

                <button type="submit" class="login-btn">
                    Register
                </button>
            </div>
        </form>

        <!-- Footer -->
        <div class="login-footer-text">
            Already have an account?
            <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>

    <style>
    /* Background */
    .min-h-screen {
        background: linear-gradient(135deg, #f4f6f9, #eef1f6) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* Card */
    .login-container {
        width: 100%;
        max-width: 450px;
        background: #ffffff;
        border-radius: 16px;
        padding: 42px 38px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    /* Logo */
    .login-logo {
        margin-bottom: 18px;
    }

    .login-logo svg {
        width: 58px;
        height: 58px;
    }

    /* Title */
    .login-title {
        font-size: 1.65rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 25px;
    }

    /* Status message */
    .login-status-message {
        padding: 12px 16px;
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
        margin-bottom: 20px;
    }

    /* Label */
    .login-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    /* Input */
    .login-input {
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .login-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
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

    /* Action Row */
    .flex.items-center.justify-between {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 26px;
    }

    /* Links */
    .login-forgot-password {
        font-size: 0.85rem;
        color: #6366f1;
        text-decoration: none;
        transition: color 0.2s ease;
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
        padding: 12px 26px;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .login-btn:hover {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(79, 70, 229, 0.3);
    }

    /* Footer */
    .login-footer-text {
        margin-top: 32px;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .login-footer-text a {
        color: #6366f1;
        font-weight: 500;
        text-decoration: none;
    }

    .login-footer-text a:hover {
        text-decoration: underline;
    }

    /* Mobile */
    @media (max-width: 480px) {
        .login-container {
            padding: 32px 26px;
        }

        .login-title {
            font-size: 1.45rem;
        }
    }
    </style>

</x-guest-layout>