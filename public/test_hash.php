<?php
$hash = '$2y$10$yHnC8X9m8y0o6bG7Yl0cY.c7jY4u0w1P5J9u5lZ1yQ8NQ0mJvQ1sW';

// var_dump(password_verify("1234", $hash));
echo password_hash("1234", PASSWORD_DEFAULT);

