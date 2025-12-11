<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Event Connect</title>
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
                <!-- Back to Home Button -->
                <a href="/" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 group transition">
                    <i class="fas fa-arrow-left text-gray-500 group-hover:text-gray-700 transition"></i>
                    <span class="group-hover:underline">Kembali ke Beranda</span>
                </a>
                
                <h1 class="text-3xl font-extrabold text-gray-900">Welcome!</h1>
                <p class="mt-1 text-sm text-gray-500">Silahkan masukkan username dan password anda</p>
            
            @if(session('success'))
                <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <form id="login-form" method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                           class="mt-2 block w-full rounded-lg border border-gray-200 ring-1 ring-gray-100 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm transition"
                           placeholder="lorem@gmail.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="mt-2 block w-full rounded-lg border border-gray-200 ring-1 ring-gray-100 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm transition"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#F4B6B6] focus:ring-[#F4B6B6]">
                        Ingat saya
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs text-gray-500 hover:text-gray-700">Lupa Password?</a>
                </div>
                
                <button type="submit" id="login-button"
                        class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-[#F4B6B6] hover:bg-[#ef9fa0] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F4B6B6] shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <span id="login-button-text">Masuk</span>
                    <span id="login-button-loading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Memproses...
                    </span>
                </button>
                
                <p class="text-center text-xs text-gray-500">
                    Belum Memiliki Akun? <a href="{{ route('register') }}" class="underline hover:text-gray-700">Daftar Disini</a>
                </p>
            </form>
            
            <script>
                // Show loading state on form submit
                document.getElementById('login-form').addEventListener('submit', function(e) {
                    const loginButton = document.getElementById('login-button');
                    const loginButtonText = document.getElementById('login-button-text');
                    const loginButtonLoading = document.getElementById('login-button-loading');
                    
                    loginButton.disabled = true;
                    loginButtonText.classList.add('hidden');
                    loginButtonLoading.classList.remove('hidden');
                });
            </script>
            </div>
        </div>
    </div>
</body>
</html>