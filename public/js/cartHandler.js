/**
 * CruseStick CartHandler - Alpine.js компонент для управления корзиной
 * Обеспечивает функциональность добавления товаров в корзину через AJAX
 */
document.addEventListener('alpine:init', () => {
    // Единый компонент для управления корзиной
    Alpine.data('cartHandler', () => ({
        quantity: 1,
        loading: false,
        showSuccess: false,
        
        // Общая функция для добавления товара в корзину
        // Если customQuantity = null, используем количество из состояния компонента
        async addToCart(productId, customQuantity = null) {
            if (this.loading) return;
            
            // Определяем источник добавления (основной товар или похожий)
            const isMainProduct = customQuantity === null;
            const quantity = isMainProduct ? this.quantity : customQuantity;
            
            // Устанавливаем состояние загрузки
            this.loading = true;
            
            try {
                // Получаем CSRF-токен
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Отправляем AJAX-запрос к API
                const response = await fetch(`/api/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: `quantity=${quantity}`,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                // Обновляем счетчик корзины, если он есть на странице
                const cartCounter = document.querySelector('.cart-counter');
                if (cartCounter) {
                    cartCounter.textContent = data.count;
                    cartCounter.classList.remove('hidden');
                }
                
                // Показываем соответствующее уведомление
                if (isMainProduct) {
                    // Для основного товара - встроенное уведомление
                    this.showSuccess = true;
                    setTimeout(() => {
                        this.showSuccess = false;
                    }, 3000);
                } else {
                    // Для похожих товаров - всплывающее уведомление
                    this.showFloatingNotification('Товар добавлен в корзину!');
                }
            } catch (error) {
                console.error('Ошибка при добавлении товара в корзину:', error);
                this.showFloatingNotification('Произошла ошибка при добавлении товара в корзину. Пожалуйста, попробуйте еще раз.', 'error');
            } finally {
                // Восстанавливаем состояние кнопки
                this.loading = false;
            }
        },
        
        // Метод для показа всплывающего уведомления
        showFloatingNotification(message, type = 'success') {
            const notification = document.createElement('div');
            const notificationId = Date.now();
            
            notification.id = `notification-${notificationId}`;
            notification.classList.add('fixed', 'bottom-4', 'right-4', 'bg-green-100', 'text-green-700', 'p-3', 'rounded', 'shadow-md', 'z-50');
            if (type === 'error') {
                notification.classList.remove('bg-green-100', 'text-green-700');
                notification.classList.add('bg-red-100', 'text-red-700');
            }
            notification.innerHTML = message;
            document.body.appendChild(notification);
            
            // Удаляем уведомление через 3 секунды
            setTimeout(() => {
                const element = document.getElementById(`notification-${notificationId}`);
                if (element) element.remove();
            }, 3000);
        }
    }));
});
