@extends('participant.layout')

@section('title', 'Submit Feedback - ' . ($event->title ?? 'Event'))

@push('styles')
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 0.5rem;
    }
    .star-rating input {
        display: none;
    }
    .star-rating label {
        cursor: pointer;
        font-size: 3rem;
        color: #d1d5db;
        transition: color 0.2s;
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #fbbf24;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Event Info Card -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-check text-green-600 text-3xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $event->title }}</h2>
                    <div class="space-y-1 text-gray-600">
                        <p><i class="fas fa-user mr-2"></i>{{ $event->organizer->full_name ?? 'Organizer' }}</p>
                        <p><i class="fas fa-calendar mr-2"></i>{{ isset($event->start_date) ? \Carbon\Carbon::parse($event->start_date)->format('F j, Y') : 'Date TBA' }}</p>
                        <p><i class="fas fa-map-marker-alt mr-2"></i>{{ $event->location ?? 'Location TBA' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Form -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Share Your Feedback</h1>
                <p class="text-gray-600">Help us improve by sharing your experience</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('participant.feedback.store', $event->id ?? 0) }}" method="POST">
                @csrf

                <!-- Rating -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-center">
                        How would you rate this event?
                    </label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1">★</label>
                    </div>
                    <p class="text-center text-sm text-gray-500 mt-2">Click on a star to rate</p>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comment -->
                <div class="mb-6">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        Your Comments <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="comment"
                        name="comment"
                        rows="6"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Share your experience, what you learned, and suggestions for improvement..."
                        required
                    >{{ old('comment') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Minimum 10 characters</p>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Certificate Info -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-certificate text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                After submitting your feedback, you'll be able to download your participation certificate!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <a href="{{ route('participant.dashboard') }}"
                       class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-3 px-4 rounded-md transition duration-150 ease-in-out text-center">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition duration-150 ease-in-out flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>

        <!-- Guidelines -->
        <div class="bg-white shadow-md rounded-lg p-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Feedback Guidelines</h3>
            <ul class="space-y-2 text-gray-600 text-sm">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Be honest and constructive in your feedback</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Mention specific aspects you liked or disliked</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Provide suggestions for improvement</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    <span>Keep your comments respectful and professional</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
