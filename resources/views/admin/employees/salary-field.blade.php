<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Normal Day Salary (₹)</label>
    <input type="number" step="0.01" min="0.01" name="daily_salary" id="daily-salary" value="{{ old('daily_salary', $salary) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
    <p class="mt-1 text-xs text-slate-500">Half day pays 0.5× and long day pays 1.5×: <span id="daily-salary-preview"></span></p>
</div>

<script>
    (() => {
        const input = document.getElementById('daily-salary');
        const preview = document.getElementById('daily-salary-preview');
        const inr = value => '₹' + value.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const paint = () => {
            const salary = parseFloat(input.value || '0');

            preview.textContent = salary > 0 ? 'Half day ' + inr(salary * 0.5) + ', Long day ' + inr(salary * 1.5) : '';
        };

        input.addEventListener('input', paint);
        paint();
    })();
</script>
