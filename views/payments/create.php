<?php
$pageTitle = 'Add Payment';
$pageSubtitle = 'Record a new payment';
require __DIR__ . '/../layouts/app_start.php';
?>

<form id="paymentForm" method="POST" action="/dental-management-system/public/payments/store" class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2 relative">
            <label class="text-sm text-slate-600">Invoice (search by ID or patient name)</label>
            <input id="invoiceSearch" type="text" placeholder="Start typing invoice id or patient name..."
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500" autocomplete="off">
            <input id="invoiceId" name="invoice_id" type="hidden" required>

            <div id="invoiceResults" class="absolute left-0 right-0 bg-white border border-slate-200 rounded-xl mt-2 max-h-48 overflow-auto z-50 hidden"></div>
        </div>

        <div>
            <label class="text-sm text-slate-600">Amount</label>
            <input type="number" name="amount" step="0.01" min="0" required
                   class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-sm text-slate-600">Payment Method</label>
            <select name="method" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select Method</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="mobile">Mobile Money</option>
            </select>
        </div>
        
        <div>
            <label class="text-sm text-slate-600">Invoice Status (mark invoice Paid or Unpaid)</label>
            <select id="invoiceStatus" name="invoice_status" required
                    class="mt-2 w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Select status</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Save Payment
        </button>
        <a href="/dental-management-system/public/payments"
           class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">
            Back
        </a>
    </div>
</form>

<?php require __DIR__ . '/../layouts/app_end.php'; ?>

<script>
    (function(){
        const input = document.getElementById('invoiceSearch');
        const hidden = document.getElementById('invoiceId');
        const results = document.getElementById('invoiceResults');
        let timer = null;

        function clearResults(){
            results.innerHTML = '';
            results.classList.add('hidden');
        }

        function render(items){
            if (!items || !items.length) { clearResults(); return; }
            results.innerHTML = '';
            items.forEach(it => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-slate-100 cursor-pointer';
                div.textContent = it.label;
                div.dataset.id = it.id;
                div.dataset.total = it.total;
                div.dataset.paid = it.paid;
                div.dataset.remaining = it.remaining;
                div.addEventListener('click', function(){
                    hidden.value = this.dataset.id;
                    input.value = this.textContent;
                    // set amount input to remaining and set max
                    const amountInput = document.querySelector('input[name="amount"]');
                    if (amountInput) {
                        amountInput.value = parseFloat(this.dataset.remaining).toFixed(2);
                        amountInput.max = parseFloat(this.dataset.remaining).toFixed(2);
                    }
                    // do not auto-change invoice status; require explicit user selection
                    // show helper text
                    showRemaining(this.dataset.remaining);
                    clearResults();
                });
                results.appendChild(div);
            });
            results.classList.remove('hidden');
        }

        function showRemaining(amount){
            let el = document.getElementById('remainingHint');
            if (!el) {
                el = document.createElement('div');
                el.id = 'remainingHint';
                el.className = 'mt-2 text-sm text-slate-600';
                const container = document.querySelector('.bg-white.rounded-2xl');
                if (container) container.appendChild(el);
            }
            el.textContent = 'Remaining balance for selected invoice: $' + parseFloat(amount).toFixed(2);
        }

        function fetchResults(q){
            fetch('/dental-management-system/public/payments/search?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(render)
                .catch(e => { console.warn(e); clearResults(); });
        }

        input.addEventListener('input', function(){
            hidden.value = '';
            const q = this.value.trim();
            if (timer) clearTimeout(timer);
            if (!q) { clearResults(); return; }
            timer = setTimeout(() => fetchResults(q), 250);
        });

        // prevent submit unless an invoice has been selected
        const form = document.getElementById('paymentForm');
        if (form) {
            form.addEventListener('submit', function(e){
                if (!hidden.value) {
                    e.preventDefault();
                    alert('Please select an invoice from the search results before submitting.');
                }
            });
        }

        document.addEventListener('click', function(e){
            if (!results.contains(e.target) && e.target !== input) {
                clearResults();
            }
        });
    })();
</script>
