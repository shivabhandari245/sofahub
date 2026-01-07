<x-guest-layout>

    <div class="login-container">
        <h2 class="login-title">Welcome Back</h2>

       
        @if (session('error'))
            <div class="login-status-message login-status-error">
                {{ session('error') }}
            </div>
        @endif

        @if (session('status'))
            <div class="login-status-message login-status-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="login-form-group">
                <label for="email" class="login-label">Email Address</label>
                <input id="email" 
                       class="login-input @error('email') login-input-error @enderror" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username">
                
                @error('email')
                    <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="login-form-group">
                <label for="password" class="login-label">Password</label>
                <input id="password" 
                       class="login-input @error('password') login-input-error @enderror"
                       type="password"
                       name="password"
                       required 
                       autocomplete="current-password">

                @error('password')
                    <div class="login-error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="login-checkbox-group">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                       name="remember">
                <label for="remember_me" class="login-label ms-2">Remember me</label>
            </div>

            <div class="flex items-center justify-between mt-4">
                @if (Route::has('password.request'))
                    <a class="login-forgot-password" href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif

                <button type="submit" class="login-btn">
                    Log in
                </button>
            </div>
        </form>

        <!-- Footer -->
        <div class="login-footer-text">
            Don't have an account? 
            @if (Route::has('register'))
                <a href="{{ route('register') }}">Sign up</a>
            @endif
        </div>   
        
    </div>  

    <style>
        .min-h-screen {
            background: linear-gradient(135deg, #e6e3df, #fafafa) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .login-title {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .login-form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .login-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .login-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .login-input-error {
            border-color: red;
        }

        .login-error-message {
            color: red;
            font-size: 13px;
            margin-top: 5px;
        }

        .login-status-message {
            margin-bottom: 15px;
            font-size: 14px;
            padding: 10px;
            border-radius: 5px;
        }

        .login-status-error {
            background-color: #ffe6e6;
            color: #cc0000;
        }

        .login-status-success {
            background-color: #e6ffe6;
            color: #009900;
        }

        .login-checkbox-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .login-btn {
            background-color: #1d72b8;
            color: #fff;
            border: none;
            padding: 10px 0;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-btn:hover {
            background-color: #155d8b;
        }

        .login-forgot-password {
            font-size: 14px;
            color: #1d72b8;
            text-decoration: underline;
        }

        .login-footer-text {
            margin-top: 20px;
            font-size: 14px;
        }

        .login-footer-text a {
            color: #1d72b8;
            text-decoration: underline;
        }
    </style>
</x-guest-layout>
