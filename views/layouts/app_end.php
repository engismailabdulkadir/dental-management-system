        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if (!empty($flashSuccess)): ?>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: <?= json_encode($flashSuccess) ?>
});
<?php endif; ?>

<?php if (!empty($flashError)): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: <?= json_encode($flashError) ?>
});
<?php endif; ?>

document.addEventListener('click', function (event) {
    var target = event.target;
    if (target && target.closest) {
        var link = target.closest('a[data-confirm]');
        if (link) {
            event.preventDefault();
            var message = link.getAttribute('data-confirm') || 'Are you sure?';
            Swal.fire({
                title: 'Please Confirm',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = link.getAttribute('href');
                }
            });
        }
    }
});

document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || !form.checkValidity || form.checkValidity()) {
        return;
    }
    event.preventDefault();
    event.stopPropagation();

    var invalid = form.querySelector(':invalid');
    if (invalid) {
        invalid.focus();
    }

    var labelText = '';
    if (invalid && invalid.id) {
        var label = form.querySelector('label[for="' + invalid.id + '"]');
        if (label) {
            labelText = label.textContent.trim();
        }
    }

    var message = labelText ? (labelText + ' is required.') : 'Please fill all required fields.';
    if (window.Swal) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: message
        });
    } else {
        alert(message);
    }
}, true);

document.addEventListener('invalid', function (event) {
    event.preventDefault();
    var input = event.target;
    if (!input) {
        return;
    }
    var form = input.form;
    var labelText = '';
    if (input.id && form) {
        var label = form.querySelector('label[for="' + input.id + '"]');
        if (label) {
            labelText = label.textContent.trim();
        }
    }
    var message = labelText ? (labelText + ' is required.') : 'Please fill all required fields.';
    if (window.Swal) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: message
        });
    } else {
        alert(message);
    }
}, true);

document.addEventListener('keydown', function (event) {
    var input = event.target;
    if (input && input.type === 'number' && event.key === '-') {
        event.preventDefault();
    }
}, true);

document.addEventListener('input', function (event) {
    var input = event.target;
    if (!input || input.type !== 'number') {
        return;
    }
    var minAttr = input.getAttribute('min');
    var minVal = minAttr !== null && minAttr !== '' ? parseFloat(minAttr) : 0;
    if (!isNaN(minVal) && String(input.value || '').trim().startsWith('-')) {
        input.value = String(minVal);
        return;
    }
    var val = parseFloat(input.value);
    if (!isNaN(val) && !isNaN(minVal) && val < minVal) {
        input.value = String(minVal);
    }
}, true);

</script>

<script src="/dental-management-system/public/assets/js/global-live-search.js"></script>

</body>
</html>
