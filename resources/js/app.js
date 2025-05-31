import axios from 'axios';
import { createApp } from 'vue';

// Expose axios globally
window.axios = axios;

// Настройка axios для CSRF защиты
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

// Функция для получения количества товаров в корзине
async function fetchCartCount() {
    try {
        const response = await axios.get('/api/cart/count');
        const cartCounters = document.querySelectorAll('.cart-counter');
        const count = response.data.count || 0;
        
        if (cartCounters.length > 0) {
            cartCounters.forEach(counter => {
                counter.textContent = count;
                
                // Показываем счетчик только если в корзине есть товары
                if (count > 0) {
                    counter.classList.remove('hidden');
                } else {
                    counter.classList.add('hidden');
                }
            });
        }
        return response.data.count;
    } catch (error) {
        console.error('Ошибка при получении количества товаров в корзине:', error);
        return 0;
    }
}

// Загружаем количество товаров в корзине при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    fetchCartCount();
});

// Вызов события обновления корзины
window.updateCart = function() {
    fetchCartCount();
    document.dispatchEvent(new CustomEvent('cart:updated'));
};

// Инициализация Vue компонентов
document.addEventListener('DOMContentLoaded', () => {
    // Создаем функцию для создания Vue-приложения с настройками
    const createVueApp = (component) => {
        const app = createApp(component);
        
        // Настройка Vue для игнорирования директив Alpine.js
        app.config.compilerOptions.isCustomElement = tag => 
            tag.startsWith('x-') || tag.includes(':') || tag.startsWith('@');
            
        return app;
    };
    
    // Компонент страницы корзины
    const cartPageElement = document.querySelector('#cart-page[data-vue-component="cart-page"]') || 
                           document.querySelector('[data-vue-component="cart-page"]') || 
                           document.getElementById('cart-page');
    
    if (cartPageElement) {
        import('./vue/cart/CartPage.vue')
            .then(module => createVueApp(module.default).mount(cartPageElement))
            .catch(error => console.error('Ошибка при загрузке компонента CartPage:', error));
    }
    
    // Компонент страницы оформления заказа
    const checkoutPageElement = document.querySelector('[data-vue-component="checkout-page"]');
    if (checkoutPageElement) {
        import('./vue/cart/CheckoutPage.vue').then(module => {
            createVueApp(module.default).mount(checkoutPageElement);
        }).catch(error => {
            console.error('Ошибка при загрузке компонента CheckoutPage:', error);
        });
    }
    
    // Компонент счетчика корзины удален, так как вместо него используется 
    // универсальная функция fetchCartCount для обновления счетчика корзины
});
