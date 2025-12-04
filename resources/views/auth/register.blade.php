<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen">
    <div class="grid grid-cols-1 md:grid-cols-5 w-full h-screen">
        <!-- Left side: image + overlay branding -->
        <div class="relative hidden md:block md:col-span-3 h-full w-full bg-center bg-cover" style="background-image:url('https://picsum.photos/1200/1600?random=21');">
            <div class="absolute inset-0 bg-gradient-to-b from-[#4B0F0F]/80 to-[#4B0F0F]/60"></div>
            <div class="relative h-full w-full flex flex-col justify-between p-10 text-white">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="text-lg font-semibold tracking-wide">Event Connect</span>
                </div>
                <div>
                    <h2 class="text-4xl leading-tight font-extrabold">Bergabung & Mulai Ikuti Berbagai Event</h2>
                    <p class="mt-4 max-w-md text-white/80">Daftar dengan cepat untuk mengelola event, memesan tiket, dan menyimpan partisipasi kamu.</p>
                </div>
                <div class="text-sm text-white/70">© {{ date('Y') }} Event Connect</div>
            </div>
        </div>

        <!-- Right side: form card -->
        <div class="bg-neutral-50 md:col-span-2 flex items-center justify-center px-6 md:px-10">
            <div class="w-full max-w-md bg-white border border-gray-100 rounded-2xl shadow-xl px-7 py-8 md:px-8 md:py-10">
                <h2 class="text-3xl font-extrabold text-gray-900">Buat Akun</h2>
                <p class="mt-1 text-sm text-gray-500">Daftar sebagai Event Organizer atau Participant</p>

        <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input id="full_name" name="full_name" type="text" autocomplete="name" required 
                           class="mt-2 block w-full rounded-lg border border-gray-200 ring-1 ring-gray-100 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm transition @error('full_name') border-red-500 @enderror" 
                           placeholder="Nama lengkap" value="{{ old('full_name') }}">
                    @error('full_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="mt-2 block w-full rounded-lg border border-gray-200 ring-1 ring-gray-100 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm transition @error('email') border-red-500 @enderror" 
                           placeholder="lorem@gmail.com" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role" name="role" required 
                            class="mt-2 block w-full rounded-lg border border-gray-200 bg-white ring-1 ring-gray-100 px-3 py-2.5 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm @error('role') border-red-500 @enderror">
                        <option value="">Pilih role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Event Organizer</option>
                        <option value="participant" {{ old('role') == 'participant' ? 'selected' : '' }}>Participant</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required 
                           class="mt-2 block w-full rounded-lg border border-gray-200 ring-1 ring-gray-100 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm transition @error('password') border-red-500 @enderror" 
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                           class="mt-2 block w-full rounded-lg border border-gray-200 ring-1 ring-gray-100 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:border-[#F4B6B6] focus:ring-2 focus:ring-[#F4B6B6] sm:text-sm transition" 
                           placeholder="Ulangi password">
                </div>
            </div>

            <!-- Role Information -->
            <div class="bg-[#F4B6B6]/15 border border-[#F4B6B6]/40 rounded-md p-4">
                <h3 class="text-sm font-medium text-[#4B0F0F] mb-2">Informasi Role:</h3>
                <div class="space-y-2 text-sm text-[#4B0F0F]/80">
                    <div class="flex items-start">
                        <i class="fas fa-crown text-[#4B0F0F] mt-0.5 mr-2"></i>
                        <div>
                            <strong>Event Organizer:</strong> Akses penuh ke dashboard, manajemen pengguna, event, dan analitik.
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-users text-[#4B0F0F] mt-0.5 mr-2"></i>
                        <div>
                            <strong>Participant:</strong> Bisa ikut event, beri feedback, unduh sertifikat, dan kelola profil.
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-[#F4B6B6] hover:bg-[#ef9fa0] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F4B6B6] shadow-sm">
                    Buat Akun
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-medium text-[#4B0F0F] hover:underline">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </form>
            </div>
        </div>
    </div>
</body>
</html>
