@extends('admin.layout')

@section('page-title', 'Profile & Certificate Settings')
@section('page-description', 'Manage your logo and signature for certificate generation')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: #B22234;">
                <i class="fas fa-user-cog text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Certificate Template Settings</h1>
                <p class="text-sm text-gray-600">Upload your logo and signature for automatic certificate generation</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Upload Sections -->
        <div class="space-y-6">
            <!-- Logo Upload Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-image text-gray-600"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Organization Logo</h2>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Upload your organization's logo. This will appear on all certificates generated for your events.
                </p>

                <!-- Current Logo Display -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                        @if(isset($user['logo_url']) && $user['logo_url'])
                            <img id="current-logo" src="{{ $user['logo_url'] }}" alt="Current Logo" class="max-h-32 mx-auto">
                        @else
                            <div class="text-gray-400 py-8">
                                <i class="fas fa-image text-4xl mb-2"></i>
                                <p class="text-sm">No logo uploaded yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Logo Upload Form -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Logo</label>
                        <input type="file" id="logo-input" accept="image/jpeg,image/png,image/jpg,image/gif"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"
                               onchange="previewImage(this, 'logo-preview')">
                        <p class="text-xs text-gray-500 mt-1">Max 2MB, Format: JPG, PNG, GIF</p>
                    </div>

                    <!-- Preview -->
                    <div id="logo-preview-container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                        <div class="border-2 border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                            <img id="logo-preview" src="" alt="Logo Preview" class="max-h-32 mx-auto">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button onclick="uploadLogo()"
                                class="flex-1 px-4 py-2 text-white rounded-lg font-medium transition-colors"
                                style="background-color: #B22234;"
                                onmouseover="this.style.backgroundColor='#8B1A28'"
                                onmouseout="this.style.backgroundColor='#B22234'">
                            <i class="fas fa-upload mr-2"></i>Upload Logo
                        </button>
                        @if(isset($user['logo_url']) && $user['logo_url'])
                            <button onclick="deleteLogo()"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Signature Upload Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-signature text-gray-600"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Digital Signature</h2>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Upload your digital signature (TTD). This will appear at the bottom of all certificates.
                </p>

                <!-- Current Signature Display -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Signature</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                        @if(isset($user['signature_url']) && $user['signature_url'])
                            <img id="current-signature" src="{{ $user['signature_url'] }}" alt="Current Signature" class="max-h-24 mx-auto">
                        @else
                            <div class="text-gray-400 py-8">
                                <i class="fas fa-pen-fancy text-4xl mb-2"></i>
                                <p class="text-sm">No signature uploaded yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Signature Upload Form -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Signature</label>
                        <input type="file" id="signature-input" accept="image/jpeg,image/png,image/jpg,image/gif"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"
                               onchange="previewImage(this, 'signature-preview')">
                        <p class="text-xs text-gray-500 mt-1">Max 2MB, Format: JPG, PNG, GIF</p>
                    </div>

                    <!-- Preview -->
                    <div id="signature-preview-container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                        <div class="border-2 border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                            <img id="signature-preview" src="" alt="Signature Preview" class="max-h-24 mx-auto">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button onclick="uploadSignature()"
                                class="flex-1 px-4 py-2 text-white rounded-lg font-medium transition-colors"
                                style="background-color: #B22234;"
                                onmouseover="this.style.backgroundColor='#8B1A28'"
                                onmouseout="this.style.backgroundColor='#B22234'">
                            <i class="fas fa-upload mr-2"></i>Upload Signature
                        </button>
                        @if(isset($user['signature_url']) && $user['signature_url'])
                            <button onclick="deleteSignature()"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Certificate Preview -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-eye text-gray-600"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Certificate Preview</h2>
                </div>
                <div class="flex gap-2">
                    <button onclick="downloadCertificatePreview()"
                            class="px-3 py-1 text-white rounded-lg text-sm font-medium transition-colors"
                            style="background-color: #B22234;"
                            onmouseover="this.style.backgroundColor='#8B1A28'"
                            onmouseout="this.style.backgroundColor='#B22234'">
                        <i class="fas fa-download mr-1"></i>Download Preview
                    </button>
                    <button onclick="refreshCertificatePreview()"
                            class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                    </button>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                Preview of how your certificate will look with your logo and signature.
            </p>

            <!-- Preview Container -->
            <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gradient-to-br from-purple-50 to-blue-50" style="min-height: 650px;">
                <iframe id="certificate-preview"
                        src="{{ route('admin.profile.preview') }}"
                        class="w-full"
                        style="height: 700px; border: none;"
                        frameborder="0">
                </iframe>
            </div>

            <!-- Info Box -->
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">How it works:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>Upload your organization logo and signature</li>
                            <li>They will automatically appear on all certificates</li>
                            <li>Certificates are generated when participants submit feedback</li>
                            <li>Each certificate is unique with QR code verification</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div id="notification-toast" class="fixed top-4 right-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg border-l-4 p-4 max-w-md">
        <div class="flex items-center gap-3">
            <i id="notification-icon" class="text-2xl"></i>
            <div class="flex-1">
                <p id="notification-message" class="font-medium"></p>
            </div>
            <button onclick="hideNotification()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<script>
// Get CSRF token
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) return token.getAttribute('content');

    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') return decodeURIComponent(value);
    }
    return '';
}

// Preview image before upload
function previewImage(input, previewId) {
    const container = document.getElementById(previewId + '-container');
    const preview = document.getElementById(previewId);

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        container.classList.add('hidden');
    }
}

// Upload logo
async function uploadLogo() {
    const input = document.getElementById('logo-input');

    if (!input.files || !input.files[0]) {
        showNotification('Please select a logo file first', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('logo', input.files[0]);

    try {
        const response = await fetch('{{ route("admin.profile.logo") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Logo uploaded successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to upload logo', 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('Failed to upload logo', 'error');
    }
}

// Upload signature
async function uploadSignature() {
    const input = document.getElementById('signature-input');

    if (!input.files || !input.files[0]) {
        showNotification('Please select a signature file first', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('signature', input.files[0]);

    try {
        const response = await fetch('{{ route("admin.profile.signature") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Signature uploaded successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to upload signature', 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('Failed to upload signature', 'error');
    }
}

// Delete logo
async function deleteLogo() {
    if (!confirm('Are you sure you want to delete your logo?')) {
        return;
    }

    try {
        const response = await fetch('{{ route("admin.profile.logo.delete") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Logo deleted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to delete logo', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Failed to delete logo', 'error');
    }
}

// Delete signature
async function deleteSignature() {
    if (!confirm('Are you sure you want to delete your signature?')) {
        return;
    }

    try {
        const response = await fetch('{{ route("admin.profile.signature.delete") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Signature deleted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to delete signature', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Failed to delete signature', 'error');
    }
}

// Refresh certificate preview
function refreshCertificatePreview() {
    const iframe = document.getElementById('certificate-preview');
    iframe.src = iframe.src;
    showNotification('Preview refreshed!', 'success');
}

// Download certificate preview
function downloadCertificatePreview() {
    try {
        const iframe = document.getElementById('certificate-preview');

        // Trigger print dialog for the iframe content
        // User can save as PDF from print dialog
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        showNotification('Opening print dialog - Save as PDF to download', 'success');
    } catch (error) {
        console.error('Download error:', error);
        showNotification('Failed to open print dialog', 'error');
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const toast = document.getElementById('notification-toast');
    const icon = document.getElementById('notification-icon');
    const messageEl = document.getElementById('notification-message');

    messageEl.textContent = message;

    if (type === 'success') {
        icon.className = 'fas fa-check-circle text-green-500 text-2xl';
        toast.querySelector('.border-l-4').style.borderColor = '#10b981';
    } else {
        icon.className = 'fas fa-exclamation-circle text-red-500 text-2xl';
        toast.querySelector('.border-l-4').style.borderColor = '#ef4444';
    }

    toast.classList.remove('hidden');

    setTimeout(() => {
        hideNotification();
    }, 5000);
}

// Hide notification
function hideNotification() {
    const toast = document.getElementById('notification-toast');
    toast.classList.add('hidden');
}
</script>
@endsection
