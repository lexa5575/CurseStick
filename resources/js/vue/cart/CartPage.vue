<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import confetti from 'canvas-confetti';

// Состояние корзины
const cartItems = ref([]);
const isLoading = ref(true);
const error = ref(null);

// Состояние купонов
const couponCode = ref('');
const appliedCoupon = ref(null);
const couponLoading = ref(false);
const couponError = ref('');
const couponSuccess = ref('');
const showCouponAnimation = ref(false);
const couponSavings = ref(0);
const cartData = ref(null);

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

const discount = computed(() => {
  return cartData.value?.total_discount || 0;
});

const finalTotal = computed(() => {
  return cartData.value?.final_total || subtotal.value;
});

// Настройка axios для всех запросов
const setupAxios = () => {
  // Устанавливаем базовый URL
  axios.defaults.baseURL = window.location.origin;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]');
  if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
  }
  
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  axios.defaults.headers.common['Accept'] = 'application/json';
  axios.defaults.withCredentials = true;
};

// Загрузка данных корзины
const fetchCart = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    setupAxios();
    const response = await axios.get('/api/cart');

    if (response.data && response.data.items) {
      cartItems.value = response.data.items;
      cartData.value = response.data;
      
      // Обновляем информацию о примененных купонах
      if (response.data.coupons && response.data.coupons.applied_coupons && response.data.coupons.applied_coupons.length > 0) {
        appliedCoupon.value = response.data.coupons.applied_coupons[0];
        couponSavings.value = response.data.coupons.total_discount;
      } else {
        appliedCoupon.value = null;
        couponSavings.value = 0;
      }
    } else {
      cartItems.value = [];
      cartData.value = null;
      appliedCoupon.value = null;
      couponSavings.value = 0;
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
    const response = await axios.patch(`/api/cart/items/${itemId}`, {
      quantity: newQuantity
    });

    // Обновляем только конкретный товар без полной перезагрузки
    const itemIndex = cartItems.value.findIndex(item => item.id === itemId);
    if (itemIndex !== -1) {
      const oldQuantity = cartItems.value[itemIndex].quantity;
      cartItems.value[itemIndex].quantity = newQuantity;
      
      // Если есть примененный купон - пересчитываем скидки
      if (appliedCoupon.value && cartItems.value[itemIndex].has_discount && cartItems.value[itemIndex].discount_amount > 0) {
        const discountPerItem = cartItems.value[itemIndex].discount_amount / oldQuantity;
        cartItems.value[itemIndex].discount_amount = discountPerItem * newQuantity;
        cartItems.value[itemIndex].final_total = (cartItems.value[itemIndex].price * newQuantity) - cartItems.value[itemIndex].discount_amount;
      }
    }

    updateCartCounter();
  } catch (err) {
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

    // Удаляем товар из локального состояния
    cartItems.value = cartItems.value.filter(item => item.id !== itemId);
    
    // Если удаляем товар и есть купон - нужно проверить совместимость
    if (appliedCoupon.value) {
      // Перезагружаем данные корзины чтобы пересчитать скидки
      await fetchCart();
    }

    updateCartCounter();
  } catch (err) {
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

  // Прямое обновление счетчика корзины в DOM с анимацией
  const cartCounters = document.querySelectorAll('.cart-counter');
  cartCounters.forEach(counter => {
    counter.textContent = newCount;
    if (newCount > 0) {
      counter.classList.remove('hidden');
    } else {
      counter.classList.add('hidden');
    }

    // Добавляем анимацию пульсации
    counter.classList.add('cart-counter-pulse');

    // Удаляем класс анимации после её завершения
    setTimeout(() => {
      counter.classList.remove('cart-counter-pulse');
    }, 300);
  });
};

// Функции для работы с купонами
const applyCoupon = async () => {
  couponLoading.value = true;
  couponError.value = '';
  couponSuccess.value = '';

  try {
    const response = await axios.post('/api/cart/apply-coupon', {
      code: couponCode.value.trim().toUpperCase()
    });

    if (response.data.success) {
      appliedCoupon.value = response.data.coupon;
      couponSavings.value = response.data.savings;
      couponSuccess.value = response.data.message;
      
      // Перезагружаем корзину чтобы получить обновленные скидки
      await fetchCart();
      
      // Запускаем красивый confetti!
      confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
      });
      
      // Дополнительный confetti через полсекунды
      setTimeout(() => {
        confetti({
          particleCount: 50,
          spread: 60,
          origin: { x: 0.3, y: 0.7 }
        });
      }, 500);
      
      setTimeout(() => {
        confetti({
          particleCount: 50,
          spread: 60,
          origin: { x: 0.7, y: 0.7 }
        });
      }, 1000);
      
      // Показываем анимацию успеха
      showSuccessAnimation();
      
      // Очищаем поле ввода
      couponCode.value = '';
    }
  } catch (err) {
    couponError.value = err.response?.data?.message || 'Invalid coupon code. Please try again.';
    shakeAnimation();
  } finally {
    couponLoading.value = false;
  }
};

const removeCoupon = async () => {
  if (!appliedCoupon.value) return;
  
  couponLoading.value = true;
  
  try {
    await axios.post('/api/cart/remove-coupon', {
      code: appliedCoupon.value.code
    });
    
    appliedCoupon.value = null;
    couponSavings.value = 0;
    couponSuccess.value = '';
    couponError.value = '';
    
    // Перезагружаем корзину чтобы получить обновленные скидки
    await fetchCart();
    
    // Показываем анимацию удаления
    showRemovalAnimation();
  } catch (err) {
    couponError.value = 'Failed to remove coupon. Please try again.';
  } finally {
    couponLoading.value = false;
  }
};

const showSuccessAnimation = () => {
  showCouponAnimation.value = true;
  setTimeout(() => {
    showCouponAnimation.value = false;
  }, 2000);
};

const showRemovalAnimation = () => {
  const couponSection = document.querySelector('.applied-coupon-section');
  if (couponSection) {
    couponSection.classList.add('transform', '-translate-y-4', 'opacity-0', 'transition-all', 'duration-500');
    setTimeout(() => {
      couponSection.classList.remove('transform', '-translate-y-4', 'opacity-0', 'transition-all', 'duration-500');
    }, 500);
  }
};

const shakeAnimation = () => {
  const couponInput = document.querySelector('.coupon-input');
  if (couponInput) {
    couponInput.classList.add('animate-pulse', 'border-red-500', 'ring-red-200');
    setTimeout(() => {
      couponInput.classList.remove('animate-pulse');
    }, 600);
  }
};

const formatCouponCode = () => {
  couponCode.value = couponCode.value.toUpperCase();
};

// Загружаем данные при монтировании компонента
onMounted(() => {
  setupAxios(); // Настраиваем axios при загрузке компонента
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
      <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">Continue
        Shopping</a>
    </div>

    <!-- Содержимое корзины -->
    <div v-else class="flex flex-col md:flex-row gap-6">
      <!-- Список товаров -->
      <div class="md:w-2/3 w-full">
        <div v-for="item in cartItems" :key="item.id" 
             class="relative border-b border-gray-200 p-4 transition-all duration-300 hover:bg-gray-50 group"
             :class="{ 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200': item.has_discount }">
          
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
                
                <div class="mt-1">
                  <span class="font-medium text-sm">{{ item.price.toFixed(2) }} $</span>
                </div>
              </div>
              <!-- Кнопка удаления для мобильных -->
              <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 text-xl p-1">
                <span>&times;</span>
              </button>
            </div>
            <!-- Количество и итоговая цена для мобильных -->
            <div class="flex items-center justify-between mt-3">
              <div class="flex items-center">
                <button @click="decreaseQuantity(item.id)"
                  class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
                  <span>-</span>
                </button>
                <span class="w-12 text-center">{{ item.quantity }}</span>
                <button @click="increaseQuantity(item.id)"
                  class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
                  <span>+</span>
                </button>
              </div>
              <div class="font-medium text-right">
                <div v-if="item.has_discount" class="space-y-1">
                  <span class="text-gray-400 text-sm line-through">{{ (item.price * item.quantity).toFixed(2) }} $</span>
                  <div class="text-green-600 font-bold text-lg">{{ Math.floor(item.final_total) }} $</div>
                </div>
                <div v-else>
                  <span class="font-bold text-lg">{{ (item.price * item.quantity).toFixed(2) }} $</span>
                </div>
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

            <div class="text-center min-w-[120px]">
              <span class="font-medium">{{ item.price.toFixed(2) }} $</span>
            </div>

            <div class="flex items-center">
              <button @click="decreaseQuantity(item.id)"
                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
                <span>-</span>
              </button>
              <span class="w-8 text-center">{{ item.quantity }}</span>
              <button @click="increaseQuantity(item.id)"
                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded">
                <span>+</span>
              </button>
            </div>

            <div class="text-center min-w-[140px]">
              <div v-if="item.has_discount" class="space-y-1">
                <span class="text-gray-400 text-sm line-through">{{ (item.price * item.quantity).toFixed(2) }} $</span>
                <div class="text-green-600 font-bold text-lg">{{ Math.floor(item.final_total) }} $</div>
              </div>
              <div v-else>
                <span class="font-bold text-lg">{{ (item.price * item.quantity).toFixed(2) }} $</span>
              </div>
            </div>

            <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 text-xl">
              <span>&times;</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Итог корзины -->
      <div class="md:w-1/3 bg-white rounded-lg shadow p-6 transition-all duration-500"
           :class="{ 'ring-2 ring-green-200 bg-green-50/30': appliedCoupon }">
        <h3 class="text-xl font-semibold mb-4">Order Summary</h3>

        <!-- Coupon Section -->
        <div class="mb-6">
          <!-- Applied Coupon Display - Компактная версия -->
          <transition 
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="transform -translate-y-4 scale-95 opacity-0"
            enter-to-class="transform translate-y-0 scale-100 opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="transform translate-y-0 scale-100 opacity-100"
            leave-to-class="transform -translate-y-4 scale-95 opacity-0"
            appear>
            <div v-if="appliedCoupon" class="applied-coupon-section bg-green-50 border border-green-200 rounded-lg p-3 mb-4 relative">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-green-800" v-text="appliedCoupon.code"></p>
                    <p class="text-xs text-green-600" v-text="appliedCoupon.name"></p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="text-green-700 font-bold">-${{ discount.toFixed(2) }}</span>
                  <button @click="removeCoupon" :disabled="couponLoading" 
                          class="text-red-500 hover:text-red-700 transition-colors p-1"
                          title="Remove coupon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </transition>

          <!-- Coupon Input Form -->
          <div v-if="!appliedCoupon" class="space-y-3">
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Have a coupon code?
              </label>
              <div class="flex space-x-2">
                <div class="flex-1 relative">
                  <input 
                    v-model="couponCode"
                    @input="formatCouponCode"
                    @keyup.enter="applyCoupon"
                    type="text" 
                    placeholder="Enter coupon code"
                    maxlength="50"
                    class="coupon-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 uppercase transform hover:scale-105 focus:scale-105"
                    :class="{ 
                      'border-red-500 ring-2 ring-red-200 animate-pulse': couponError,
                      'border-green-500 ring-2 ring-green-200': couponSuccess,
                      'opacity-50 cursor-not-allowed': couponLoading
                    }"
                    :disabled="couponLoading"
                  >
                  
                  <!-- Loading spinner -->
                  <div v-if="couponLoading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                  </div>
                </div>
                
                <button 
                  @click="applyCoupon"
                  :disabled="couponLoading || !couponCode.trim()"
                  class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden shadow-lg font-medium transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5 active:scale-95"
                >
                  <span v-if="!couponLoading">Apply</span>
                  <div v-else class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Applying...
                  </div>
                </button>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="couponError" class="bg-red-50 border border-red-200 rounded-lg p-3 transform transition-all duration-300 animate-pulse">
              <div class="flex items-center">
                <svg class="w-4 h-4 text-red-500 mr-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-red-700">{{ couponError }}</span>
              </div>
            </div>

            <!-- Success Message -->
            <div v-if="couponSuccess" class="bg-green-50 border border-green-200 rounded-lg p-3 transform transition-all duration-300 animate-pulse">
              <div class="flex items-center">
                <svg class="w-4 h-4 text-green-500 mr-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-green-700">{{ couponSuccess }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Breakdown -->
        <div class="space-y-3 mb-4">
          <div class="flex justify-between text-sm">
            <span>Item count:</span>
            <span>{{ totalItems }}</span>
          </div>

          <div class="flex justify-between text-sm">
            <span>Subtotal:</span>
            <span>{{ subtotal.toFixed(2) }} $</span>
          </div>

          <!-- Discount Row -->
          <div v-if="appliedCoupon && discount > 0" class="flex justify-between text-sm text-green-600">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
              Discount (<span v-text="appliedCoupon.code"></span>):
            </span>
            <span class="font-medium">-{{ Math.floor(discount) }} $</span>
          </div>
        </div>

        <div class="flex justify-between text-lg font-semibold border-t border-gray-200 pt-4 mt-4">
          <span>Total:</span>
          <span class="relative">
            <span v-if="appliedCoupon && discount > 0" class="text-gray-400 line-through text-sm mr-2">
              {{ subtotal.toFixed(2) }} $
            </span>
            <span :class="{ 'text-green-600': appliedCoupon && discount > 0 }">
              {{ Math.floor(finalTotal) }} $
            </span>
          </span>
        </div>


        <a href="/checkout"
          class="block w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-center py-4 rounded-xl font-bold mt-6 relative overflow-hidden group shadow-lg transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 active:scale-95">
          <span class="relative z-10 flex items-center justify-center gap-2">
            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6"></path>
            </svg>
            Proceed to Checkout
            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-blue-800 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
        </a>
      </div>
    </div>
  </div>
</template>
