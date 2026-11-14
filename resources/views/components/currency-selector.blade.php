<div class="relative inline-block">
    <label for="currency-select" class="sr-only">Select Currency</label>
    <select id="currency-select" 
            class="appearance-none bg-white border-2 border-gray-200 hover:border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md">
        <option value="USD" {{ auth()->user()->currency === 'USD' ? 'selected' : '' }}>🇺🇸 USD - US Dollar</option>
        <option value="EUR" {{ auth()->user()->currency === 'EUR' ? 'selected' : '' }}>🇪🇺 EUR - Euro</option>
        <option value="GBP" {{ auth()->user()->currency === 'GBP' ? 'selected' : '' }}>🇬🇧 GBP - British Pound</option>
        <option value="JPY" {{ auth()->user()->currency === 'JPY' ? 'selected' : '' }}>🇯🇵 JPY - Japanese Yen</option>
        <option value="CNY" {{ auth()->user()->currency === 'CNY' ? 'selected' : '' }}>🇨🇳 CNY - Chinese Yuan</option>
        <option value="INR" {{ auth()->user()->currency === 'INR' ? 'selected' : '' }}>🇮🇳 INR - Indian Rupee</option>
        <option value="CAD" {{ auth()->user()->currency === 'CAD' ? 'selected' : '' }}>🇨🇦 CAD - Canadian Dollar</option>
        <option value="AUD" {{ auth()->user()->currency === 'AUD' ? 'selected' : '' }}>🇦🇺 AUD - Australian Dollar</option>
        <option value="CHF" {{ auth()->user()->currency === 'CHF' ? 'selected' : '' }}>🇨🇭 CHF - Swiss Franc</option>
        <option value="SEK" {{ auth()->user()->currency === 'SEK' ? 'selected' : '' }}>🇸🇪 SEK - Swedish Krona</option>
        <option value="NZD" {{ auth()->user()->currency === 'NZD' ? 'selected' : '' }}>🇳🇿 NZD - New Zealand Dollar</option>
        <option value="KRW" {{ auth()->user()->currency === 'KRW' ? 'selected' : '' }}>🇰🇷 KRW - South Korean Won</option>
        <option value="SGD" {{ auth()->user()->currency === 'SGD' ? 'selected' : '' }}>🇸🇬 SGD - Singapore Dollar</option>
        <option value="HKD" {{ auth()->user()->currency === 'HKD' ? 'selected' : '' }}>🇭🇰 HKD - Hong Kong Dollar</option>
        <option value="NOK" {{ auth()->user()->currency === 'NOK' ? 'selected' : '' }}>🇳🇴 NOK - Norwegian Krone</option>
        <option value="MXN" {{ auth()->user()->currency === 'MXN' ? 'selected' : '' }}>🇲🇽 MXN - Mexican Peso</option>
        <option value="BRL" {{ auth()->user()->currency === 'BRL' ? 'selected' : '' }}>🇧🇷 BRL - Brazilian Real</option>
        <option value="ZAR" {{ auth()->user()->currency === 'ZAR' ? 'selected' : '' }}>🇿🇦 ZAR - South African Rand</option>
        <option value="RUB" {{ auth()->user()->currency === 'RUB' ? 'selected' : '' }}>🇷🇺 RUB - Russian Ruble</option>
        <option value="TRY" {{ auth()->user()->currency === 'TRY' ? 'selected' : '' }}>🇹🇷 TRY - Turkish Lira</option>
        <option value="AED" {{ auth()->user()->currency === 'AED' ? 'selected' : '' }}>🇦🇪 AED - UAE Dirham</option>
        <option value="SAR" {{ auth()->user()->currency === 'SAR' ? 'selected' : '' }}>🇸🇦 SAR - Saudi Riyal</option>
        <option value="THB" {{ auth()->user()->currency === 'THB' ? 'selected' : '' }}>🇹🇭 THB - Thai Baht</option>
        <option value="IDR" {{ auth()->user()->currency === 'IDR' ? 'selected' : '' }}>🇮🇩 IDR - Indonesian Rupiah</option>
        <option value="MYR" {{ auth()->user()->currency === 'MYR' ? 'selected' : '' }}>🇲🇾 MYR - Malaysian Ringgit</option>
        <option value="PHP" {{ auth()->user()->currency === 'PHP' ? 'selected' : '' }}>🇵🇭 PHP - Philippine Peso</option>
        <option value="PLN" {{ auth()->user()->currency === 'PLN' ? 'selected' : '' }}>🇵🇱 PLN - Polish Zloty</option>
        <option value="DKK" {{ auth()->user()->currency === 'DKK' ? 'selected' : '' }}>🇩🇰 DKK - Danish Krone</option>
        <option value="CZK" {{ auth()->user()->currency === 'CZK' ? 'selected' : '' }}>🇨🇿 CZK - Czech Koruna</option>
        <option value="HUF" {{ auth()->user()->currency === 'HUF' ? 'selected' : '' }}>🇭🇺 HUF - Hungarian Forint</option>
        <option value="UGX" {{ auth()->user()->currency === 'UGX' ? 'selected' : '' }}>🇺🇬 UGX - Ugandan Shilling</option>
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>

<script>
(function() {
    const select = document.getElementById('currency-select');
    if (!select) return;

    const storageKey = 'fintrack_currency_user_{{ auth()->id() ?? 'guest' }}';
    const serverCurrency = '{{ strtoupper(auth()->user()->currency ?? 'USD') }}';

    // Check per-user localStorage preference on mount
    let savedCurrency = null;
    try {
        savedCurrency = localStorage.getItem(storageKey);
    } catch (e) {}

    if (savedCurrency && Array.from(select.options).some(o => o.value === savedCurrency)) {
        select.value = savedCurrency;
    } else if (serverCurrency && Array.from(select.options).some(o => o.value === serverCurrency)) {
        select.value = serverCurrency;
    }

    window.currentAppCurrency = select.value;

    select.addEventListener('change', async function(e) {
        const currency = e.target.value.toUpperCase();
        
        // Save per-user preference in localStorage immediately
        try {
            localStorage.setItem(storageKey, currency);
        } catch (err) {}

        window.currentAppCurrency = currency;
        const userCurrencyMeta = document.querySelector('meta[name="user-currency"]');
        if (userCurrencyMeta) {
            userCurrencyMeta.content = currency;
        }

        // Update active currency in client state immediately
        if (window.setAppCurrency) {
            window.setAppCurrency(currency);
        }

        // Show loading state on select
        select.disabled = true;
        select.style.opacity = '0.6';
        
        try {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const currencyUrl = '{{ route('dashboard.currency', [], false) }}';
            const response = await fetch(currencyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ currency: currency })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.currency && window.setAppCurrency) {
                    window.setAppCurrency(data.currency, data.currency_symbol);
                }
            }

            // Refresh the dashboard cards with the newly selected currency
            if (window.refreshDashboard) {
                await window.refreshDashboard(currency);
            }

            // Show success notification if Toast is available
            if (window.Toast) {
                window.Toast.success('Currency updated to ' + currency);
            }
        } catch (error) {
            console.error('Error updating currency on server:', error);
            // Even if network call had an issue, keep the user-chosen currency in the UI and localStorage
            if (window.refreshDashboard) {
                window.refreshDashboard(currency);
            }
        } finally {
            select.disabled = false;
            select.style.opacity = '1';
        }
    });
})();
</script>
