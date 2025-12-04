<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - Event Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen">
    <div class="grid grid-cols-1 md:grid-cols-5 w-full h-screen">
        <!-- Left side: full-height image with overlay and branding -->
        <div class="relative hidden md:block md:col-span-3 h-full w-full bg-center bg-cover" style="background-image:url('https://picsum.photos/1200/1600?random=12');">
            <div class="absolute inset-0 bg-gradient-to-b from-[#4B0F0F]/80 to-[#4B0F0F]/60"></div>
            <div class="relative h-full w-full flex flex-col justify-between p-10 text-white">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="text-lg font-semibold tracking-wide">Event Connect</span>
                </div>
                <div>
                    <h2 class="text-4xl leading-tight font-extrabold">Temukan & Kelola Event Favoritmu</h2>
                    <p class="mt-4 max-w-md text-white/80">Jelajahi ratusan event, pesan tiket, dan pantau partisipasimu—semua dalam satu tempat.</p>
                </div>
                <div class="text-sm text-white/70">© {{ date('Y') }} Event Connect</div>
            </div>
        </div>
        <!-- Right side: elevated form card -->
        <div class="bg-neutral-50 md:col-span-2 flex items-center justify-center px-6 md:px-10">
            <div class="w-full max-w-md bg-white border border-gray-100 rounded-2xl shadow-xl px-7 py-8 md:px-8 md:py-10">
                <!-- Back to Login Button -->
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 group transition">
                    <i class="fas fa-arrow-left text-gray-500 group-hover:text-gray-700 transition"></i>
                    <span class="group-hover:underline">Kembali ke Login</span>
                </a>
                
                <h1 class="text-3xl font-extrabold text-gray-900">Lupa Password?</h1>
                <p class="mt-1 text-sm text-gray-500">Masukkan email Anda untuk mendapatkan token reset</p>
                
                <div id="success-message" class="mt-4 hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <p class="font-semibold mb-2">Token Reset Berhasil Dibuat!</p>
                    <p class="text-sm mb-3">Salin token di bawah ini dan gunakan untuk reset password:</p>
                    <div class="bg-white border border-green-300 rounded p-3 mb-3">
                        <p id="token-display" class="font-mono text-xs break-all"></p>
                    </div>
                    <p class="text-xs mb-2">Atau klik link di bawah:</p>
                    <a id="reset-link" href="#" target="_blank" class="text-xs text-green-600 hover:text-green-800 underline block break-all"></a>
                    <p class="text-xs text-gray-600 mt-3">Token berlaku selama 1 jam</p>
                </div>
                
                <div id="error-message" class="mt-4 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
                
                <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5"
                                placeholder="lorem@gmail.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-[#F4B6B6]">
                            Buat Token Reset
                        </button>
                    </form>

                    @if(session('message') || session('reset_token'))
                        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p class="font-semibold mb-2">{{ session('message') }}</p>

                            @if(session('reset_token'))
                                <p class="text-sm mb-2">Token (untuk testing):</p>
                                <div class="bg-white border border-green-300 rounded p-3 mb-3">
                                    <p class="font-mono text-xs break-all">{{ session('reset_token') }}</p>
                                </div>

                                <p class="text-sm mb-1">Atau buka link:</p>
                                <a href="{{ session('reset_url') }}" class="text-green-600 underline break-all" target="_blank">
                                    {{ session('reset_url') }}
                                </a>
                                <p class="text-xs text-gray-600 mt-2">Token berlaku 1 jam.</p>
                            @endif
                        </div>
                    @endif
                
                <script>
                    document.getElementById('forgot-form').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        // Reset error messages
                        document.getElementById('error-message').classList.add('hidden');
                        document.getElementById('success-message').classList.add('hidden');
                        document.getElementById('email-error').classList.add('hidden');
                        
                        // Get form data
                        const email = document.getElementById('email').value;
                        
                        // Disable button and show loading
                        const submitButton = document.getElementById('submit-button');
                        const buttonText = document.getElementById('button-text');
                        const buttonLoading = document.getElementById('button-loading');
                        submitButton.disabled = true;
                        buttonText.classList.add('hidden');
                        buttonLoading.classList.remove('hidden');
                        
                        try {
                            console.log('Sending forgot password request for:', email);
                            
                            const response = await fetch('/api/auth/forgot-password', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    email: email
                                })
                            });
                            
                            console.log('Response status:', response.status);
                            
                            let data;
                            try {
                                data = await response.json();
                                console.log('Response data:', data);
                            } catch (parseError) {
                                console.error('Failed to parse JSON response:', parseError);
                                throw new Error('Invalid response from server');
                            }
                            
                            if (!response.ok) {
                                // Handle validation errors
                                if (data.errors) {
                                    if (data.errors.email) {
                                        document.getElementById('email-error').textContent = data.errors.email[0];
                                        document.getElementById('email-error').classList.remove('hidden');
                                    }
                                } else {
                                    // Show general error message
                                    const errorMessage = document.getElementById('error-message');
                                    errorMessage.textContent = data.message || 'Gagal membuat token reset. Silakan coba lagi.';
                                    errorMessage.classList.remove('hidden');
                                }
                                submitButton.disabled = false;
                                buttonText.classList.remove('hidden');
                                buttonLoading.classList.add('hidden');
                                return;
                            }
                            
                            if (data.success && data.data) {
                                console.log('Token created successfully');
                                // Show success message with token
                                document.getElementById('token-display').textContent = data.data.token;
                                document.getElementById('reset-link').href = data.data.reset_url;
                                document.getElementById('reset-link').textContent = data.data.reset_url;
                                document.getElementById('success-message').classList.remove('hidden');
                                document.getElementById('forgot-form').style.display = 'none';
                            } else {
                                throw new Error(data.message || 'Gagal membuat token reset');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            const errorMessage = document.getElementById('error-message');
                            errorMessage.textContent = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
                            errorMessage.classList.remove('hidden');
                            submitButton.disabled = false;
                            buttonText.classList.remove('hidden');
                            buttonLoading.classList.add('hidden');
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</body>
</html>