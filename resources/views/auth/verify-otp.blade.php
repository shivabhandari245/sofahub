<x-guest-layout>
    <h2>Verify OTP</h2>

    @if(session('message'))
        <div class="success">{{ session('message') }}</div>
    @endif

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <div class="form-group">
            <label for="otp">OTP Code</label>
            <input type="text" id="otp" name="otp" required maxlength="6">
            @error('otp')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn">Verify</button>
    </form>

    <div class="resend-container">
        <button id="resend-btn" class="btn resend-btn">Resend OTP</button>
        <span id="countdown-text"></span>
        <div id="resend-message" style="margin-top:10px;"></div>
        <div id="resend-alert" style="margin-top:10px; display:none;"></div>
    </div>

    <style>
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { padding: 10px; width: 100%; background-color: #1d72b8; color: white; border: none; border-radius: 5px; margin-top: 10px; cursor: pointer; }
        .btn:hover { background-color: #155d8b; }
        .resend-btn { border: 2px; text-decoration: underline; cursor: pointer; font-size: 14px; width: auto; }
        .resend-btn:disabled { color: gray; cursor: not-allowed; text-decoration: none; }
        .error { color: red; font-size: 13px; margin-top: 5px; display: block; }
        .success { color: green; font-size: 14px; margin-bottom: 15px; text-align: center; }
        #resend-alert { color: darkorange; font-weight: bold; }
    </style>

    <script>
        const resendBtn = document.getElementById('resend-btn');
        const countdownText = document.getElementById('countdown-text');
        const resendMessage = document.getElementById('resend-message');
        const resendAlert = document.getElementById('resend-alert');

        let countdown = 0;
        let interval;

        function startCountdown(seconds) {
            countdown = seconds;
            resendBtn.disabled = true;
            updateCountdownText();

            interval = setInterval(() => {
                countdown--;
                updateCountdownText();

                if (countdown <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    countdownText.textContent = '';
                    resendAlert.style.display = 'none';
                }
            }, 1000);
        }

        function updateCountdownText() {
            countdownText.textContent = countdown > 0 ? ` (${countdown}s)` : '';
        }

        resendBtn.addEventListener('click', function() {
            resendMessage.textContent = '';
            resendAlert.style.display = 'none';
            fetch("{{ route('otp.resend') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if(data.message) {
                    resendMessage.textContent = data.message;
                    resendMessage.style.color = 'green';
                    startCountdown(30);
                } else if(data.error) {
                    resendMessage.textContent = '';
                    resendAlert.textContent = data.error;
                    resendAlert.style.display = 'block';

                   
                    const match = data.error.match(/(\d+) seconds/);
                    if(match) {
                        startCountdown(parseInt(match[1]));
                    } else {
                        resendBtn.disabled = true;
                    }
                }
            })
            .catch(err => {
                resendMessage.textContent = 'Something went wrong.';
                resendMessage.style.color = 'red';
            });
        });
    </script>
</x-guest-layout>
