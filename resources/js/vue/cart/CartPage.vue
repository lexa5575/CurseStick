<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Состояние корзины
const cartItems = ref([]);
const isLoading = ref(true);
const error = ref(null);

// Сообщения об успехе/ошибке (больше не используются для отображения)
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
  error.value = null;
  
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    const cartCookie = document.cookie.split('; ').find(row => row.startsWith('cart_session='));
    if (cartCookie) {
      const cartId = cartCookie.split('=')[1];
      axios.defaults.headers.common['X-Cart-ID'] = cartId;
    }
    
    const response = await axios.get('/api/cart');
    
    if (response.data && response.data.items) {
      cartItems.value = response.data.items;
    } else {
      cartItems.value = [];
    }
  } catch (err) {
    error.value = 'Failed to load cart. Please try again later.';
    // errorMessage.value = 'Failed to load cart. Please try again later.';
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
    
    const itemIndex = cartItems.value.findIndex(item => item.id === itemId);
    if (itemIndex !== -1) {
      cartItems.value[itemIndex].quantity = newQuantity;
      // successMessage.value = 'Quantity updated successfully';
    }
    
    updateCartCounter();
  } catch (err) {
    // errorMessage.value = 'Failed to update item quantity';
    console.error('Failed to update item quantity:', err);
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
    
    cartItems.value = cartItems.value.filter(item => item.id !== itemId);
    
    // successMessage.value = 'Item removed from cart';
    
    updateCartCounter();
  } catch (err) {
    // errorMessage.value = 'Failed to remove item from cart';
    console.error('Failed to remove item from cart:', err);
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
  fetchCart();
  
  // Убираем проверку URL параметров для сообщений
  // const urlParams = new URLSearchParams(window.location.search);
  // if (urlParams.has('success')) {
  //   successMessage.value = urlParams.get('success');
  // }
  // if (urlParams.has('error')) {
  //   errorMessage.value = urlParams.get('error');
  // }
});
</script>

<template>
  <div class="w-full max-w-7xl mx-auto px-4">
    <!-- Заголовок страницы -->
    <h1 class="text-2xl font-bold mb-6">Shopping Cart</h1>
    
    <!-- Загрузка -->
    <div v-if="isLoading" class="text-center py-8">
      <p class="text-gray-600">Loading your cart...</p>
    </div>
    
    <!-- Ошибка -->
    <div v-else-if="error" class="text-center py-8">
      <p class="text-red-600 mb-2">{{ error }}</p>
      <!-- Можно оставить кнопку отладки или убрать -->
    </div>
    
    <!-- Пустая корзина -->
    <div v-else-if="totalItems === 0" class="text-center py-8">
      <p class="text-gray-600 mb-4">Your cart is empty</p>
      <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">Continue Shopping</a>
    </div>
    
    <!-- Содержимое корзины -->
    <div v-else class="flex flex-col md:flex-row gap-6">
      <!-- Список товаров -->
      <div class="md:w-2/3 w-full">
        <div v-for="item in cartItems" :key="item.id" class="border-b border-gray-200 p-4">
          <!-- Мобильный layout -->
          <div class="md:hidden">
            <div class="flex items-start gap-3">
              <!-- Изображение и информация о товаре -->
              <div class="flex-shrink-0">
                <img :src="item.product.image_url" :alt="item.product.name" class="w-16 h-16 object-cover rounded">
              </div>
              <div class="flex-1">
                <h3 class="font-medium text-sm">{{ item.product.name }}</h3>
                <p class="text-xs text-gray-500">{{ item.product.category.name }}</p>
                <p class="font-medium text-sm mt-1">{{ item.price.toFixed(2) }} $</p>
              </div>
              <!-- Кнопка удаления для мобильных -->
              <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 text-xl p-1">
                <span>&times;</span>
              </button>
            </div>
            <!-- Количество и итоговая цена для мобильных -->
            <div class="flex items-center justify-between mt-3">
              <div class="flex items-center">
                <button @click="decreaseQuantity(item.id)" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
                  <span>-</span>
                </button>
                <span class="w-12 text-center">{{ item.quantity }}</span>
                <button @click="increaseQuantity(item.id)" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
                  <span>+</span>
                </button>
              </div>
              <div class="font-medium">
                Total: {{ (item.price * item.quantity).toFixed(2) }} $
              </div>
            </div>
          </div>
          
          <!-- Десктопный layout -->
          <div class="hidden md:flex items-center gap-4">
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
      </div>
      
      <!-- Итог корзины -->
      <div class="md:w-1/3 bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-semibold mb-4">Order Summary</h3>
        
        <div class="flex justify-between text-sm mb-3">
          <span>Item count:</span>
          <span>{{ totalItems }}</span>
        </div>
        
        <div class="flex justify-between text-sm mb-3">
          <span>Subtotal:</span>
          <span>{{ subtotal.toFixed(2) }} $</span>
        </div>
        
        <div class="flex justify-between text-lg font-semibold border-t border-gray-200 pt-4 mt-4">
          <span>Total:</span>
          <span>{{ subtotal.toFixed(2) }} $</span>
        </div>
        
        <a href="/checkout" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-md font-medium mt-4 transition-colors">
          Proceed to Checkout
        </a>
      </div>
    </div>
  </div>
</template>
