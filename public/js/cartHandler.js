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
                
                // Обновляем все счетчики корзины на странице с анимацией
                const cartCounters = document.querySelectorAll('.cart-counter');
                cartCounters.forEach(counter => {
                    counter.textContent = data.count;
                    counter.classList.remove('hidden');
                    
                    // Добавляем анимацию пульсации
                    counter.classList.add('cart-counter-pulse');
                    
                    // Удаляем класс анимации после её завершения
                    setTimeout(() => {
                        counter.classList.remove('cart-counter-pulse');
                    }, 300);
                });
                
                // Показываем соответствующее уведомление
                if (isMainProduct) {
                    // Для основного товара - встроенное уведомление
                    this.showSuccess = true;
                    setTimeout(() => {
                        this.showSuccess = false;
                    }, 3000);
                } else {
                    // Для похожих товаров и других страниц - всплывающее уведомление
                    this.showFloatingNotification('Product added to cart!');
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
            notification.style.cssText = 'position: fixed; bottom: 20px; right: 20px; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); font-weight: 600; z-index: 9999;';
            
            if (type === 'error') {
                notification.style.backgroundColor = '#ef4444';
                notification.style.color = 'white';
            } else {
                notification.style.backgroundColor = '#10b981';
                notification.style.color = 'white';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Удаляем уведомление через 3 секунды
            setTimeout(() => {
                const element = document.getElementById(`notification-${notificationId}`);
                if (element) element.remove();
            }, 3000);
        }
    }));
});
