<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Event Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen">
    <div class="grid grid-cols-1 md:grid-cols-5 w-full h-screen">
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
                <div class="text-sm text-white/70">© <?php echo e(date('Y')); ?> Event Connect</div>
            </div>
        </div>

        <div class="bg-neutral-50 md:col-span-2 flex items-center justify-center px-6 md:px-10">
            <div class="w-full max-w-md bg-white border border-gray-100 rounded-2xl shadow-xl px-7 py-8 md:px-8 md:py-10">
                <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 group transition">
                    <i class="fas fa-arrow-left text-gray-500 group-hover:text-gray-700 transition"></i>
                    <span class="group-hover:underline">Kembali ke Login</span>
                </a>

                <h1 class="text-3xl font-extrabold text-gray-900">Lupa Password?</h1>
                <p class="mt-1 text-sm text-gray-500">Masukkan email Anda untuk mendapatkan token reset</p>

                <?php if(session('message') || session('reset_token')): ?>
                    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <p class="font-semibold mb-2"><?php echo e(session('message')); ?></p>

                        <?php if(session('reset_token')): ?>
                            <p class="text-sm mb-2">Token (untuk testing):</p>
                            <div class="bg-white border border-green-300 rounded p-3 mb-3">
                                <p class="font-mono text-xs break-all"><?php echo e(session('reset_token')); ?></p>
                            </div>

                            <p class="text-sm mb-1">Atau buka link:</p>
                            <a href="<?php echo e(session('reset_url')); ?>" class="text-green-600 underline break-all" target="_blank">
                                <?php echo e(session('reset_url')); ?>

                            </a>
                            <p class="text-xs text-gray-600 mt-2">Token berlaku 1 jam.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div id="error-message"></div>

                <form method="POST" action="<?php echo e(route('password.email')); ?>" class="mt-8 space-y-5">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required
                               class="mt-2 block w-full rounded-lg border border-gray-200 px-3 py-2.5"
                               placeholder="lorem@gmail.com">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 rounded-lg text-white font-semibold bg-[#F4B6B6]">
                        Buat Token Reset
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH C:\xampp\htdocs\frontend\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>