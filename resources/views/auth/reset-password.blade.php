<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Event Connect</title>
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
                
                <h1 class="text-3xl font-extrabold text-gray-900">Reset Password</h1>
                <p class="mt-1 text-sm text-gray-500">Masukkan password baru Anda</p>
                
                <div id="success-message" class="mt-4 hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <p class="font-semibold mb-2">Password Berhasil Direset!</p>
                    <p class="mb-3">Anda akan diarahkan ke halaman login dalam beberapa detik...</p>
                </div>
                
                <div id="error-message" class="mt-4 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
                
                <form method="POST" action="{{ route('password.update') }}" id="reset-form" class="mt-8 space-y-5">
                        @csrf
                        <input type="hidden" name="token" value="{{ request('token') }}">
                        <input type="hidden" name="email" value="{{ request('email') }}">

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                            <input id="password" name="password" type="password" required
                                class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5"
                                placeholder="••••••••">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5"
                                placeholder="••••••••">
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-[#F4B6B6]">
                            Reset Password
                        </button>
                    </form>

                    @if(session('success'))
                        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                
                <script>
                    // Toggle password visibility
                    document.querySelectorAll('.toggle-password').forEach(button => {
                        button.addEventListener('click', function() {
                            const input = document.getElementById('password');
                            const icon = this.querySelector('i');
                            if (input.type === 'password') {
                                input.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                input.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        });
                    });
                    
                    document.querySelectorAll('.toggle-password-confirm').forEach(button => {
                        button.addEventListener('click', function() {
                            const input = document.getElementById('password_confirmation');
                            const icon = this.querySelector('i');
                            if (input.type === 'password') {
                                input.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                input.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        });
                    });
                    
                    document.getElementById('reset-form').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        // Reset error messages
                        document.getElementById('error-message').classList.add('hidden');
                        document.getElementById('success-message').classList.add('hidden');
                        document.getElementById('password-error').classList.add('hidden');
                        document.getElementById('password_confirmation-error').classList.add('hidden');
                        
                        // Get form data
                        const token = document.getElementById('token').value;
                        const email = document.getElementById('email').value;
                        const password = document.getElementById('password').value;
                        const password_confirmation = document.getElementById('password_confirmation').value;
                        
                        // Validate inputs
                        if (!token) {
                            document.getElementById('error-message').textContent = 'Token tidak ditemukan. Gunakan link dari halaman forgot password.';
                            document.getElementById('error-message').classList.remove('hidden');
                            return;
                        }
                        
                        if (!email) {
                            document.getElementById('error-message').textContent = 'Email tidak ditemukan. Gunakan link dari halaman forgot password.';
                            document.getElementById('error-message').classList.remove('hidden');
                            return;
                        }
                        
                        // Disable button and show loading
                        const submitButton = document.getElementById('submit-button');
                        const buttonText = document.getElementById('button-text');
                        const buttonLoading = document.getElementById('button-loading');
                        submitButton.disabled = true;
                        buttonText.classList.add('hidden');
                        buttonLoading.classList.remove('hidden');
                        
                        try {
                            console.log('Sending reset password request');
                            
                            const response = await fetch('/api/auth/reset-password', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    token: token,
                                    email: email,
                                    password: password,
                                    password_confirmation: password_confirmation
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
                                    if (data.errors.password) {
                                        document.getElementById('password-error').textContent = data.errors.password[0];
                                        document.getElementById('password-error').classList.remove('hidden');
                                    }
                                    if (data.errors.password_confirmation) {
                                        document.getElementById('password_confirmation-error').textContent = data.errors.password_confirmation[0];
                                        document.getElementById('password_confirmation-error').classList.remove('hidden');
                                    }
                                    if (data.errors.email) {
                                        const errorMessage = document.getElementById('error-message');
                                        errorMessage.textContent = data.errors.email[0];
                                        errorMessage.classList.remove('hidden');
                                    }
                                    if (data.errors.token) {
                                        const errorMessage = document.getElementById('error-message');
                                        errorMessage.textContent = 'Link reset password telah kadaluarsa. Silakan minta link baru.';
                                        errorMessage.classList.remove('hidden');
                                    }
                                } else {
                                    // Show general error message
                                    const errorMessage = document.getElementById('error-message');
                                    errorMessage.textContent = data.message || 'Gagal mereset password. Silakan coba lagi.';
                                    errorMessage.classList.remove('hidden');
                                }
                                submitButton.disabled = false;
                                buttonText.classList.remove('hidden');
                                buttonLoading.classList.add('hidden');
                                return;
                            }
                            
                            if (data.success) {
                                console.log('Password reset successful');
                                // Show success message
                                document.getElementById('success-message').classList.remove('hidden');
                                document.getElementById('reset-form').style.display = 'none';
                                
                                // Redirect to login after 2 seconds
                                setTimeout(() => {
                                    window.location.href = '{{ route("login") }}';
                                }, 2000);
                            } else {
                                throw new Error(data.message || 'Gagal mereset password');
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