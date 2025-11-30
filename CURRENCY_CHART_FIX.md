# 7-Day Spending Chart Currency Fix

## Issue
The 7-day spending chart on the dashboard was displaying a hardcoded `$` symbol on the y-axis, regardless of the user's selected currency.

## Solution
Updated the chart to dynamically use the user's selected currency symbol on both:
- Y-axis labels
- Tooltip values

## Changes Made

### File: `resources/views/dashboard.blade.php`

1. **Added Currency Variables**
   ```javascript
   const currencySymbol = '{{ currency_symbol() }}';
   const currencyCode = '{{ auth()->user()->currency ?? 'USD' }}';
   const symbolAfterCurrencies = ['EUR', 'SEK', 'NOK', 'DKK', 'CZK', 'HUF', 'PLN'];
   const symbolAfter = symbolAfterCurrencies.includes(currencyCode);
   ```

2. **Updated Chart Y-Axis Ticks**
   - Now uses `currencySymbol` variable instead of hardcoded `$`
   - Respects currency formatting rules (symbol before or after amount)
   - Adds thousand separators with `toLocaleString()`

3. **Added Tooltip Formatting**
   - Tooltips now show values with the correct currency symbol
   - Consistent formatting with y-axis labels

4. **Updated Helper Functions**
   - `formatCurrency()` - Now uses user's currency symbol
   - `formatCurrencyDecimals()` - Now uses user's currency symbol
   - Both functions respect symbol position (before/after)

## Currency Symbol Positioning

### Symbol Before Amount (Default)
- USD: $1,000
- GBP: £1,000
- JPY: ¥1,000
- INR: ₹1,000
- etc.

### Symbol After Amount
- EUR: 1,000 €
- SEK: 1,000 kr
- NOK: 1,000 kr
- DKK: 1,000 kr
- CZK: 1,000 Kč
- HUF: 1,000 Ft
- PLN: 1,000 zł

## Testing
1. Change your currency in the dashboard using the currency selector
2. The 7-day spending chart should immediately update to show the new currency symbol
3. Hover over bars to see tooltips with correct currency formatting
4. Y-axis labels should display the correct currency symbol

## Example Output

**Before (USD):**
- Y-axis: $0, $5000, $10000, $15000
- Tooltip: $12,500

**After (EUR):**
- Y-axis: 0 €, 5000 €, 10000 €, 15000 €
- Tooltip: 12,500 €

**After (GBP):**
- Y-axis: £0, £5000, £10000, £15000
- Tooltip: £12,500

## Related Files
- `app/Helpers/CurrencyHelper.php` - Currency symbol mapping
- `app/Helpers/helpers.php` - `currency_symbol()` helper function
- `resources/views/dashboard.blade.php` - Chart implementation
