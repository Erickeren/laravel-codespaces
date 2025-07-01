@extends('layouts.app')

@section('title', 'Shift Scheduler - Main Dashboard')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Shift Scheduler Dashboard</h2>
        <p class="text-gray-600">Manage your 4-person shift rotation system with alternating 4 days on/off cycles.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Input Forms -->
        <div class="lg:col-span-1 space-y-6">
            <!-- People Input Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 1: Add People</h3>
                <form action="{{ route('people.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        @for($i = 1; $i <= 4; $i++)
                            <div>
                                <label for="person{{ $i }}" class="block text-sm font-medium text-gray-700">
                                    Person {{ $i }}
                                </label>
                                <input 
                                    type="text" 
                                    id="person{{ $i }}" 
                                    name="people[]" 
                                    value="{{ old('people.' . ($i-1), $people->get($i-1)->name ?? '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                    placeholder="Enter name"
                                    required
                                >
                            </div>
                        @endfor
                    </div>
                    <button 
                        type="submit" 
                        class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-primary focus:ring-offset-2 transition duration-200"
                    >
                        Save People
                    </button>
                </form>
            </div>

            <!-- Schedule Generation Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Step 2: Generate Schedule</h3>
                <form action="{{ route('schedule.generate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">
                            Start Date
                        </label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="{{ old('start_date', now()->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            required
                        >
                    </div>
                    <div>
                        <label for="duration_weeks" class="block text-sm font-medium text-gray-700">
                            Duration (Weeks)
                        </label>
                        <select 
                            id="duration_weeks" 
                            name="duration_weeks" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                            required
                        >
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('duration_weeks', 4) == $i ? 'selected' : '' }}>
                                    {{ $i }} week{{ $i > 1 ? 's' : '' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <button 
                        type="submit" 
                        class="w-full bg-secondary text-white py-2 px-4 rounded-md hover:bg-green-700 focus:ring-2 focus:ring-secondary focus:ring-offset-2 transition duration-200"
                        {{ $people->count() < 4 ? 'disabled' : '' }}
                    >
                        Generate Schedule
                    </button>
                    @if($people->count() < 4)
                        <p class="text-sm text-gray-500">Please add 4 people before generating schedule.</p>
                    @endif
                </form>
            </div>

            <!-- Export Options -->
            @if($schedules->isNotEmpty())
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Step 3: Export</h3>
                    <a 
                        href="{{ route('schedule.export') }}" 
                        class="w-full bg-gray-800 text-white py-2 px-4 rounded-md hover:bg-gray-900 focus:ring-2 focus:ring-gray-800 focus:ring-offset-2 transition duration-200 inline-block text-center"
                    >
                        📊 Export to Excel
                    </a>
                </div>
            @endif
        </div>

        <!-- Right Column: Schedule Display -->
        <div class="lg:col-span-2">
            @if($schedules->isNotEmpty())
                <!-- Summary Info -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">{{ $people->count() }}</div>
                            <div class="text-sm text-gray-500">People</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-secondary">{{ $schedules->where('status', 'on_duty')->count() / 2 }}</div>
                            <div class="text-sm text-gray-500">Total Days</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">2</div>
                            <div class="text-sm text-gray-500">Shifts per Day</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">4</div>
                            <div class="text-sm text-gray-500">Day Rotation</div>
                        </div>
                    </div>
                </div>

                <!-- On-Duty Schedule Table -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">📅 On-Duty Schedule</h3>
                        <p class="text-sm text-gray-600">People currently working (Shift A: 6AM-6PM, Shift B: 6PM-6AM)</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift A (6AM-6PM)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift B (6PM-6AM)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($schedules->where('status', 'on_duty')->groupBy('date') as $date => $daySchedules)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($date)->format('l') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $shiftA = $daySchedules->where('shift_type', 'A')->first();
                                            @endphp
                                            @if($shiftA)
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm font-medium">
                                                    {{ $shiftA->person->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $shiftB = $daySchedules->where('shift_type', 'B')->first();
                                            @endphp
                                            @if($shiftB)
                                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm font-medium">
                                                    {{ $shiftB->person->name }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Off-Duty Schedule Table -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">🏠 Off-Duty Schedule</h3>
                        <p class="text-sm text-gray-600">People who are off-duty and resting</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Off-Duty Personnel</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($schedules->where('status', 'off_duty')->groupBy('date') as $date => $daySchedules)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($date)->format('l') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($daySchedules as $schedule)
                                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-sm">
                                                        {{ $schedule->person->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Schedule Generated</h3>
                    <p class="text-gray-600 mb-6">Add 4 people and generate a schedule to get started.</p>
                    
                    <!-- Instructions -->
                    <div class="bg-blue-50 rounded-lg p-6 text-left max-w-md mx-auto">
                        <h4 class="font-medium text-blue-900 mb-3">How it works:</h4>
                        <ul class="text-sm text-blue-800 space-y-2">
                            <li>• Each person works 4 consecutive days, then rests 4 days</li>
                            <li>• 2 people are on duty each day (Shift A & B)</li>
                            <li>• Shift A: 6:00 AM - 6:00 PM</li>
                            <li>• Shift B: 6:00 PM - 6:00 AM (next day)</li>
                            <li>• Rotation automatically continues the cycle</li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection