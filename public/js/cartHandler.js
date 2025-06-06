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
                // Всегда показываем всплывающее уведомление на английском
                this.showFloatingNotification('Product added to cart!');
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
            const container = document.getElementById('notification-container');
            if (!container) {
                console.error('The #notification-container element was not found in the DOM.');
                return;
            }

            // Создаем само уведомление
            const notification = document.createElement('div');
            notification.classList.add('p-4', 'rounded-lg', 'shadow-xl', 'text-white', 'font-semibold');
            
            if (type === 'error') {
                notification.classList.add('bg-red-500');
            } else {
                notification.classList.add('bg-green-500');
            }
            notification.textContent = message;

            // Очищаем контейнер от старых уведомлений и добавляем новое
            container.innerHTML = '';
            container.appendChild(notification);
            
            // Удаляем уведомление через 3 секунды
            setTimeout(() => {
                // Убедимся, что удаляем именно то уведомление, которое мы создали
                if (container.contains(notification)) {
                    container.removeChild(notification);
                }
            }, 3000);
        }
    }));
});
