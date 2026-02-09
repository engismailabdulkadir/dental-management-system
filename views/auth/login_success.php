<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Logging in...</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
  Swal.fire({
    icon: 'success',
    title: 'Login Successful',
    text: 'Welcome back!',
    confirmButtonText: 'OK'
  }).then(function() {
    window.location.href = <?= json_encode($target) ?>;
  });
</script>

</body>
</html>
