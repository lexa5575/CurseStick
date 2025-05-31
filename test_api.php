<?php

// Подключаем автозагрузчик Composer
require __DIR__ . '/vendor/autoload.php';

// Загружаем приложение Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Импортируем необходимые классы
use App\Http\Controllers\Api\CartController;
use Illuminate\Http\Request;

// Создаем экземпляр контроллера
$controller = app()->make(CartController::class);

// Вызываем метод index() напрямую
$response = $controller->index();

// Получаем JSON-данные
$jsonData = $response->getContent();

// Выводим результат
echo "=== Тестирование API корзины ===\n\n";
echo "Ответ API-контроллера:\n";
echo json_encode(json_decode($jsonData), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

// Проверка маршрутов
echo "=== Проверка маршрутов API ===\n\n";
$routes = Route::getRoutes();
echo "Маршруты, начинающиеся с 'api/cart':\n";
foreach ($routes as $route) {
    $uri = $route->uri();
    if (str_starts_with($uri, 'api/cart')) {
        echo "- {$route->methods()[0]} {$uri} => " . json_encode($route->getAction()['uses']) . "\n";
    }
}
echo "\n";

// Проверка имени класса, используемого в itemable_type
echo "=== Проверка имени класса модели Product ===\n\n";
echo "Полное имя класса Product: " . \App\Models\Product::class . "\n";
echo "Проверка существования класса: " . (class_exists(\App\Models\Product::class) ? "Класс существует" : "Класс не существует!") . "\n";
echo "\n";

echo "=== Тестирование завершено ===\n";
