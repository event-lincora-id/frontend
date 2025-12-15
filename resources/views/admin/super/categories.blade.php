@extends('admin.super-layout')

@section('title', 'Categories Management')
@section('page-title', 'Categories Management')
@section('page-description', 'Manage all event categories across the platform')

@section('content')

<!-- Flash Messages -->
@if(session('success'))
<div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    </div>
</div>
@endif

@if($errors->any())
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-red-700 font-medium">Please fix the following errors:</p>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

{{-- Search & Sort Bar --}}
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 bg-white p-4 rounded-md shadow-sm border border-gray-200">
    {{-- Search Form --}}
    <form id="searchForm" action="{{ route('super.admin.categories') }}" method="GET" class="flex items-center w-full sm:w-auto">
        <div class="relative w-full sm:w-72">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input 
                type="text" 
                name="search" 
                id="searchInput"
                class="form-control w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                placeholder="Search category..." 
                value="{{ request('search') }}"
                autocomplete="off"
            >
        </div>
    </form>

    {{-- Sort Dropdown --}}
    <form id="sortForm" action="{{ route('super.admin.categories') }}" method="GET" class="flex items-center w-full sm:w-auto">
        <input type="hidden" name="search" id="sortSearchValue" value="{{ request('search') }}">
        <select 
            name="sort" 
            class="form-select w-full sm:w-auto border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2 px-3"
            onchange="document.getElementById('sortForm').submit()"
        >
            <option value="">Sort by</option>
            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A–Z)</option>
            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z–A)</option>
            <option value="events_desc" {{ request('sort') == 'events_desc' ? 'selected' : '' }}>Most Events</option>
            <option value="events_asc" {{ request('sort') == 'events_asc' ? 'selected' : '' }}>Fewest Events</option>
            <option value="active" {{ request('sort') == 'active' ? 'selected' : '' }}>Active Categories</option>
            <option value="inactive" {{ request('sort') == 'inactive' ? 'selected' : '' }}>Inactive Categories</option>
        </select>
    </form>
</div>

{{-- Script: Auto Search --}}
<script>
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const sortSearchValue = document.getElementById('sortSearchValue');
    let typingTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            sortSearchValue.value = searchInput.value;
            searchForm.submit();
        }, 500);
    });
</script>

<div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Event Categories</h3>
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add New Category
            </button>
        </div>
    </div>

    @if(isset($error))
        <div class="p-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $error }}
            </div>
        </div>
    @endif

    <!-- Categories Grid -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($categories as $category)
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category['color'] ?? '#3B82F6' }}"></div>
                        <h4 class="text-lg font-medium text-gray-900">{{ $category['name'] ?? 'Unnamed' }}</h4>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick='openEditModal(@json($category))' class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="confirmDelete({{ $category['id'] ?? 0 }})" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="text-sm text-gray-600 mb-4">
                    {{ $category['description'] ?? 'No description available' }}
                </div>
                
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">
                        <i class="fas fa-calendar mr-1"></i>
                        {{ $category['events_count'] ?? 0 }} events (all organizers)
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($category['is_active'] ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ($category['is_active'] ?? true) ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-tags text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No categories found</h3>
                <p class="text-gray-500">
                    @if(request('search') || request('sort'))
                        No categories match your search criteria.
                    @else
                        There are no categories on the platform yet.
                    @endif
                </p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex justify-center gap-2">
            @if($categories->onFirstPage())
                <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Previous</button>
            @else
                <a href="{{ $categories->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Previous</a>
            @endif

            @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                @if($page == $categories->currentPage())
                    <button class="px-3 py-1 bg-blue-600 text-white rounded font-semibold" disabled>{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">{{ $page }}</a>
                @endif
            @endforeach

            @if($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100">Next</a>
            @else
                <button class="px-3 py-1 text-gray-400 cursor-not-allowed" disabled>Next</button>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="categoryForm" method="POST">
                @csrf
                <div id="methodField"></div>
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Add New Category</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Category Name *</label>
                                    <input type="text" name="name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2">
                                </div>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2"></textarea>
                                </div>
                                
                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" name="color" id="color" value="#3B82F6" class="h-10 w-16 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <span class="ml-3 text-sm text-gray-500">Choose a color for this category</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Category
                    </button>
                    <button type="button" onclick="closeCategoryModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Category</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this category? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('categoryForm').action = '{{ route("super.admin.categories.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('color').value = '#3B82F6';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function openEditModal(category) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryForm').action = `/super-admin/categories/${category.id}`;
    document.getElementById('methodField').innerHTML = '@method("PUT")';
    document.getElementById('name').value = category.name;
    document.getElementById('description').value = category.description || '';
    document.getElementById('color').value = category.color || '#3B82F6';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

function confirmDelete(categoryId) {
    document.getElementById('deleteForm').action = `/super-admin/categories/${categoryId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('categoryModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCategoryModal();
    }
});

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
