<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Создание нового администратора...\n";

// Создаем нового пользователя с учетом структуры базы данных
$user = new \App\Models\User();
$user->first_name = 'Admin';
$user->last_name = 'User';
$user->email = 'admin@crusestick.com';
$user->password = bcrypt('Admin123!');
$user->email_verified_at = now();
$user->save();

echo "Пользователь создан: admin@crusestick.com (пароль: Admin123!)\n";

// Проверяем и назначаем роли, если доступны
if (class_exists('\Spatie\Permission\Models\Role')) {
    echo "Spatie Permission найден. Назначаем роль администратора...\n";
    
    // Убедимся, что роль существует
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    
    // Назначаем роль пользователю
    $user->assignRole('admin');
    
    echo "Роль администратора назначена.\n";
} else {
    echo "Spatie Permission не найден. Роли не назначены.\n";
}

echo "Готово! Теперь вы можете войти в админ-панель с email: admin@crusestick.com и паролем: Admin123!\n";
