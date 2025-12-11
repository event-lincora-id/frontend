@extends('participant.layout')

@section('title', 'Profile Settings - Event Connect')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
            <p class="mt-2 text-gray-600">Manage your personal information and account settings</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle mr-3 text-red-600 mt-0.5"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6">
            <!-- Profile Information Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-user-circle mr-3 text-red-600"></i>
                        Profile Information
                    </h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('participant.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Avatar Section -->
                            <div class="flex items-center space-x-6">
                                <div class="flex-shrink-0">
                                    @if(isset($user['avatar']) && $user['avatar'])
                                        <img src="{{ $user['avatar'] }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                    @else
                                        <div class="w-24 h-24 bg-gradient-to-br from-red-400 to-pink-500 rounded-full flex items-center justify-center border-4 border-gray-200">
                                            <span class="text-3xl font-bold text-white">
                                                {{ strtoupper(substr($user['full_name'] ?? $user['name'] ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $user['full_name'] ?? $user['name'] ?? 'User' }}</h3>
                                    <p class="text-sm text-gray-600">{{ $user['email'] ?? '' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Member since {{ isset($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->format('M Y') : 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-6"></div>

                            <!-- Full Name -->
                            <div>
                                <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="full_name" 
                                       id="full_name" 
                                       value="{{ old('full_name', $user['full_name'] ?? $user['name'] ?? '') }}" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            </div>

                            <!-- Email (Read Only) -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ $user['email'] ?? '' }}" 
                                       readonly
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                                <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input type="text" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $user['phone'] ?? '') }}" 
                                       placeholder="+62812345678"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            </div>

                            <!-- Bio -->
                            <div>
                                <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Bio
                                </label>
                                <textarea name="bio" 
                                          id="bio" 
                                          rows="4" 
                                          placeholder="Tell us about yourself..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none">{{ old('bio', $user['bio'] ?? '') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('participant.dashboard') }}" 
                               class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-md hover:shadow-lg">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-lock mr-3 text-blue-600"></i>
                        Change Password
                    </h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('participant.profile.change-password') }}" method="POST" id="changePasswordForm">
                        @csrf

                        <div class="space-y-6">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Current Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="current_password" 
                                           id="current_password" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12">
                                    <button type="button" 
                                            onclick="togglePassword('current_password')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-eye" id="current_password_icon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    New Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           required
                                           minlength="8"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12">
                                    <button type="button" 
                                            onclick="togglePassword('password')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-eye" id="password_icon"></i>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Confirm New Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation" 
                                           required
                                           minlength="8"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12">
                                    <button type="button" 
                                            onclick="togglePassword('password_confirmation')"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-eye" id="password_confirmation_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md hover:shadow-lg">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-gray-600"></i>
                        Account Information
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-1">Account Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>Active
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-1">Role</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-user mr-2"></i>{{ ucfirst($user['role'] ?? 'Participant') }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-1">Member Since</p>
                            <p class="text-gray-900">{{ isset($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->format('d F Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-1">Last Updated</p>
                            <p class="text-gray-900">{{ isset($user['updated_at']) ? \Carbon\Carbon::parse($user['updated_at'])->format('d F Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password confirmation validation
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    
    if (password !== confirmation) {
        e.preventDefault();
        alert('Password and confirmation do not match!');
        return false;
    }
});
</script>
@endsection
@endsection
