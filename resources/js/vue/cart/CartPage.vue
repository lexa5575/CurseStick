<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Состояние корзины
const cartItems = ref([]);
const isLoading = ref(true);
const error = ref(null);

// Сообщения об успехе/ошибке
const successMessage = ref('');
const errorMessage = ref('');

// Вычисляемые свойства
const totalItems = computed(() => cartItems.value.length);
const subtotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.price * item.quantity);
  }, 0);
});

// Загрузка данных корзины
const fetchCart = async () => {
  isLoading.value = true;
  error.value = null; // Сбрасываем ошибку при новой загрузке
  
  try {
    // Проверяем наличие CSRF-токена
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    // Добавляем специальные заголовки
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    // Если есть куки с ID корзины, добавляем его в заголовок
    const cartCookie = document.cookie.split('; ').find(row => row.startsWith('cart_session='));
    if (cartCookie) {
      const cartId = cartCookie.split('=')[1];
      axios.defaults.headers.common['X-Cart-ID'] = cartId;
    }
    
    // Основной запрос к API
    const response = await axios.get('/api/cart');
    
    // Проверяем наличие данных в ответе
    if (response.data && response.data.items) {
      cartItems.value = response.data.items;
    } else {
      cartItems.value = [];
    }
  } catch (err) {
    error.value = 'Не удалось загрузить корзину. Пожалуйста, попробуйте позже.';
    errorMessage.value = 'Не удалось загрузить корзину. Пожалуйста, попробуйте позже.';
  } finally {
    isLoading.value = false;
  }
};

// Обновление количества товара
const updateQuantity = async (itemId, newQuantity) => {
  if (newQuantity < 1) return;
  
  try {
    await axios.patch(`/api/cart/items/${itemId}`, {
      quantity: newQuantity
    });
    
    // Обновляем локальное состояние
    const itemIndex = cartItems.value.findIndex(item => item.id === itemId);
    if (itemIndex !== -1) {
      cartItems.value[itemIndex].quantity = newQuantity;
      successMessage.value = 'Количество товара успешно обновлено';
    }
    
    // Обновляем счетчик корзины
    updateCartCounter();
  } catch (err) {
    errorMessage.value = 'Не удалось обновить количество товара';
  }
};

// Уменьшение количества товара
const decreaseQuantity = async (itemId) => {
  const itemIndex = cartItems.value.findIndex(item => item.id === itemId);
  if (itemIndex === -1) return;
  
  const currentQuantity = cartItems.value[itemIndex].quantity;
  if (currentQuantity === 1) {
    // Если количество = 1, удаляем товар
    removeItem(itemId);
  } else {
    // Иначе уменьшаем количество
    updateQuantity(itemId, currentQuantity - 1);
  }
};

// Увеличение количества товара
const increaseQuantity = async (itemId) => {
  const itemIndex = cartItems.value.findIndex(item => item.id === itemId);
  if (itemIndex === -1) return;
  
  const currentQuantity = cartItems.value[itemIndex].quantity;
  updateQuantity(itemId, currentQuantity + 1);
};

// Удаление товара из корзины
const removeItem = async (itemId) => {
  try {
    await axios.delete(`/api/cart/items/${itemId}`);
    
    // Удаляем товар из локального состояния
    cartItems.value = cartItems.value.filter(item => item.id !== itemId);
    
    // Показываем сообщение об успехе
    successMessage.value = 'Товар удален из корзины';
    
    // Обновляем счетчик корзины
    updateCartCounter();
  } catch (err) {
    errorMessage.value = 'Не удалось удалить товар из корзины';
  }
};

// Вспомогательная функция для обновления счетчика корзины
const updateCartCounter = () => {
  const newCount = cartItems.value.reduce((total, item) => total + item.quantity, 0);
  
  // Отправляем событие обновления корзины
  document.dispatchEvent(new CustomEvent('cart:updated', {
    detail: { count: newCount }
  }));
  
  // Прямое обновление счетчика корзины в DOM
  const cartCounters = document.querySelectorAll('.cart-counter');
  cartCounters.forEach(counter => {
    counter.textContent = newCount;
    if (newCount > 0) {
      counter.classList.remove('hidden');
    } else {
      counter.classList.add('hidden');
    }
  });
};

// Загружаем данные при монтировании компонента
onMounted(() => {
  // Загружаем данные корзины при монтировании компонента
  fetchCart();
  
  // Проверяем, есть ли параметры URL для отображения сообщений
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('success')) {
    successMessage.value = urlParams.get('success');
  }
  if (urlParams.has('error')) {
    errorMessage.value = urlParams.get('error');
  }
});
</script>

<template>
  <div class="w-full max-w-7xl mx-auto px-4">
    <!-- Заголовок страницы -->
    <h1 class="text-2xl font-bold mb-6">Корзина</h1>
    
    <!-- Примечание: этот компонент проявится только при загрузке Vue, а noscript блок остается в Blade-шаблоне -->
    
    <!-- Сообщения об успехе/ошибке -->
    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline">{{ successMessage }}</span>
      <button @click="successMessage = ''" class="absolute top-0 right-0 px-4 py-3">&times;</button>
    </div>
    
    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline">{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="absolute top-0 right-0 px-4 py-3">&times;</button>
    </div>
    
    <!-- Отладочная информация -->
    <div v-if="false" class="mb-6">
      <h3 class="text-lg font-bold mb-2">Отладочная информация:</h3>
      <div class="bg-gray-100 p-4 rounded mb-4 font-mono text-sm overflow-auto max-h-64">
        <p><strong>Состояние загрузки:</strong> {{ isLoading ? 'Загрузка...' : 'Завершена' }}</p>
        <p><strong>Ошибка:</strong> {{ error || 'Нет' }}</p>
        <p><strong>Количество элементов:</strong> {{ cartItems.length }}</p>
        <pre>{{ JSON.stringify(cartItems, null, 2) }}</pre>
        <button @click="showDebug = false" class="mt-2 px-2 py-1 bg-red-500 text-white rounded text-xs">Скрыть отладку</button>
        <button @click="fetchCart()" class="ml-2 mt-2 px-2 py-1 bg-blue-500 text-white rounded text-xs">Обновить данные</button>
      </div>
    </div>
    
    <!-- Загрузка -->
    <div v-if="isLoading" class="text-center py-8">
      <p class="text-gray-600">Загрузка корзины...</p>
    </div>
    
    <!-- Ошибка -->
    <div v-else-if="error" class="text-center py-8">
      <p class="text-red-600 mb-2">{{ error }}</p>
      <button @click="showDebug = !showDebug" class="mt-2 px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">{{ showDebug ? 'Скрыть отладку' : 'Показать отладку' }}</button>
    </div>
    
    <!-- Пустая корзина -->
    <div v-else-if="totalItems === 0" class="text-center py-8">
      <p class="text-gray-600 mb-4">Ваша корзина пуста</p>
      <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">Перейти к покупкам</a>
    </div>
    
    <!-- Содержимое корзины -->
    <div v-else class="flex flex-col md:flex-row gap-6">
      <!-- Список товаров -->
      <div class="md:w-2/3 w-full">
        <div v-for="item in cartItems" :key="item.id" class="flex items-center p-4 border-b border-gray-200 gap-4">
          <div class="flex-shrink-0">
            <img :src="item.product.image_url" :alt="item.product.name" class="w-20 h-20 object-cover rounded">
          </div>
          
          <div class="flex-1">
            <h3 class="font-medium">{{ item.product.name }}</h3>
            <p class="text-xs text-gray-500">{{ item.product.category.name }}</p>
          </div>
          
          <div class="font-medium">
            {{ item.price.toFixed(2) }} $
          </div>
          
          <div class="flex items-center">
            <button @click="decreaseQuantity(item.id)" class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
              <span>-</span>
            </button>
            <span class="w-8 text-center">{{ item.quantity }}</span>
            <button @click="increaseQuantity(item.id)" class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
              <span>+</span>
            </button>
          </div>
          
          <div class="font-medium">
            {{ (item.price * item.quantity).toFixed(2) }} $
          </div>
          
          <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 text-xl">
            <span>&times;</span>
          </button>
        </div>
      </div>
      
      <!-- Итог корзины -->
      <div class="md:w-1/3 bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold mb-4">Итог заказа</h3>
        
        <div class="flex justify-between text-sm mb-3">
          <span>Количество товаров:</span>
          <span>{{ totalItems }}</span>
        </div>
        
        <div class="flex justify-between text-sm mb-3">
          <span>Подытог:</span>
          <span>{{ subtotal.toFixed(2) }} $</span>
        </div>
        
        <div class="flex justify-between text-lg font-semibold border-t border-gray-200 pt-4 mt-4">
          <span>Итого:</span>
          <span>{{ subtotal.toFixed(2) }} $</span>
        </div>
        
        <a href="/checkout" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-md font-medium mt-4 transition-colors">
          Оформить заказ
        </a>
      </div>
    </div>
  </div>
</template>
