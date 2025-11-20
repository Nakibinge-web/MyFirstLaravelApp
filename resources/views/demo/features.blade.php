@extends('layouts.app')

@section('page-title', 'UI/UX Features Demo')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">🎨 UI/UX Features Demo</h1>

    <!-- Toast Notifications Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Toast Notifications</h2>
        <div class="space-x-4">
            <button onclick="Toast.success('This is a success message!')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Show Success
            </button>
            <button onclick="Toast.error('This is an error message!')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Show Error
            </button>
            <button onclick="Toast.info('This is an info message!')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Show Info
            </button>
        </div>
    </div>

    <!-- Loading States Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Loading States</h2>
        <button id="loadingDemo" onclick="
            LoadingState.show(this);
            setTimeout(() => LoadingState.hide(document.getElementById('loadingDemo')), 3000);
        " class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Click to Show Loading (3s)
        </button>
    </div>

    <!-- Keyboard Shortcuts Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Keyboard Shortcuts</h2>
        <p class="text-gray-700 mb-4">Try these keyboard shortcuts:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700">
            <li><kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">?</kbd> - Show keyboard shortcuts help</li>
            <li><kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Ctrl + K</kbd> - Quick search</li>
            <li><kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Ctrl + N</kbd> - New transaction (on transactions page)</li>
            <li><kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Esc</kbd> - Close modals</li>
        </ul>
    </div>

    <!-- Tooltips Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Tooltips</h2>
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" data-tooltip="This is a helpful tooltip!">
            Hover Over Me
        </button>
    </div>

    <!-- Utility Functions Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Utility Functions</h2>
        <div class="space-y-4">
            <div>
                <p class="text-gray-700 mb-2">Currency Formatting:</p>
                <code class="bg-gray-100 px-3 py-2 rounded block">formatCurrency(1234.56) = <span id="currencyDemo"></span></code>
            </div>
            <div>
                <p class="text-gray-700 mb-2">Number Formatting:</p>
                <code class="bg-gray-100 px-3 py-2 rounded block">formatNumber(1234567) = <span id="numberDemo"></span></code>
            </div>
            <div>
                <p class="text-gray-700 mb-2">Date Formatting:</p>
                <code class="bg-gray-100 px-3 py-2 rounded block">formatDate('2025-11-15') = <span id="dateDemo"></span></code>
            </div>
            <div>
                <button onclick="copyToClipboard('Hello from Financial Tracker!')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Copy Text to Clipboard
                </button>
            </div>
        </div>
    </div>

    <!-- Progress Bars Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Animated Progress Bars</h2>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span>Budget Progress</span>
                    <span>75%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="progress-bar bg-blue-500 h-3 rounded-full" style="width: 75%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span>Goal Progress</span>
                    <span>90%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="progress-bar bg-green-500 h-3 rounded-full" style="width: 90%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span>Warning Level</span>
                    <span>95%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="progress-bar bg-red-500 h-3 rounded-full" style="width: 95%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skeleton Loading Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Skeleton Loading</h2>
        <div class="space-y-3">
            <div class="skeleton h-4 rounded"></div>
            <div class="skeleton h-4 rounded w-3/4"></div>
            <div class="skeleton h-4 rounded w-1/2"></div>
        </div>
    </div>

    <!-- Card Hover Effects Demo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6 card-hover">
            <h3 class="text-lg font-bold mb-2">Hover Card 1</h3>
            <p class="text-gray-600">Hover over me to see the elevation effect!</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 card-hover">
            <h3 class="text-lg font-bold mb-2">Hover Card 2</h3>
            <p class="text-gray-600">I lift up when you hover!</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 card-hover">
            <h3 class="text-lg font-bold mb-2">Hover Card 3</h3>
            <p class="text-gray-600">Smooth transitions everywhere!</p>
        </div>
    </div>

    <!-- Mobile Responsive Demo -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4">Mobile Responsive</h2>
        <p class="text-gray-700 mb-4">Resize your browser window to see:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700">
            <li>Mobile menu appears on small screens</li>
            <li>Navigation collapses to hamburger menu</li>
            <li>Cards stack vertically on mobile</li>
            <li>Tables become scrollable</li>
        </ul>
    </div>
</div>

<script>
    // Initialize demo values
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('currencyDemo').textContent = formatCurrency(1234.56);
        document.getElementById('numberDemo').textContent = formatNumber(1234567);
        document.getElementById('dateDemo').textContent = formatDate('2025-11-15');
        
        // Initialize tooltips
        initTooltips();
    });
</script>
@endsection
