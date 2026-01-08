<x-guest-layout>
    <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-gray-800 dark:bg-gray-900 shadow-lg rounded-lg overflow-hidden">
        <div class="flex justify-center mb-6">
            <x-application-logo class="w-20 h-20 fill-current text-gray-400" />
        </div>

        <h2 class="text-xl font-semibold text-center text-gray-200 mb-6">Verify OTP</h2>

        @if(session('message'))
            <div class="text-green-500 text-center mb-4">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="text-red-500 text-center mb-4">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-6">
            @csrf
            <div>
                <label for="otp" class="block text-sm font-medium text-gray-300">OTP Code</label>
                <input type="text" id="otp" name="otp" required maxlength="6"
                       class="mt-1 block w-full px-4 py-3 border border-gray-600 rounded-md shadow-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                              sm:text-base dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('otp')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md
                           focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                Verify
            </button>
        </form>

        <div class="mt-6 text-center">
            <button id="resend-btn" 
                    class="text-blue-500 underline text-sm hover:text-blue-700 disabled:text-gray-500 disabled:cursor-not-allowed">
                Resend OTP
            </button>
            <span id="countdown-text" class="ml-2 text-gray-400 text-sm"></span>

            <div id="resend-message" class="text-green-500 mt-2 text-sm"></div>
            <div id="resend-alert" class="text-orange-500 font-semibold mt-2 text-sm hidden"></div>
        </div>
    </div>

    <script>
        const resendBtn = document.getElementById('resend-btn');
        const countdownText = document.getElementById('countdown-text');
        const resendMessage = document.getElementById('resend-message');
        const resendAlert = document.getElementById('resend-alert');

        let countdown = 0;
        let interval = null;

        function clearCountdown() {
            if (interval) { clearInterval(interval); interval = null; }
        }

        function startCountdown(seconds) {
            clearCountdown();
            countdown = seconds;
            resendBtn.disabled = true;
            updateCountdownText();

            interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearCountdown();
                    resendBtn.disabled = false;
                    countdownText.textContent = '';
                    resendAlert.classList.add('hidden');
                    return;
                }
                updateCountdownText();
            }, 1000);
        }

        function updateCountdownText() {
            countdownText.textContent = countdown > 0 ? `(${countdown}s)` : '';
        }

        resendBtn.addEventListener('click', function () {
            resendBtn.disabled = true; 
            resendMessage.textContent = '';
            resendAlert.classList.add('hidden');

            fetch("{{ route('otp.resend') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    resendMessage.textContent = data.message;
                    resendMessage.classList.remove('text-red-600');
                    resendMessage.classList.add('text-green-600');
                    startCountdown(30);
                } else if (data.error) {
                    resendAlert.textContent = data.error;
                    resendAlert.classList.remove('hidden');
                    const match = data.error.match(/(\d+)\s*seconds?/);
                    if (match) startCountdown(parseInt(match[1], 10));
                }
            })
            .catch(async (err) => {
                resendBtn.disabled = false;
                try {
                    const data = await err.json();
                    resendAlert.textContent = data.message || 'Too many requests. Please wait.';
                } catch {
                    resendAlert.textContent = 'Server error. Please try again later.';
                }
                resendAlert.classList.remove('hidden');
            });
        });
    </script>
</x-guest-layout>
