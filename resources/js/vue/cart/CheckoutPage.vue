<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// State
const isLoading = ref(true);
const error = ref(null);
const cartItems = ref([]);
const formData = ref({
  name: '',
  email: '',
  phone: '',
  company: '',
  street: '',
  house: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'United States',
  comment: '',
  payment_method: '',
  ageVerified: false
});

// Messages
const successMessage = ref('');
const errorMessage = ref('');

// Computed Properties
const subtotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.price * item.quantity);
  }, 0);
});

// Validation State
const isSubmitting = ref(false);
const validationErrors = ref({});

// Form validation function
const validateForm = () => {
  validationErrors.value = {};
  let isValid = true;
  
  if (!formData.value.name.trim()) {
    validationErrors.value.name = 'Name is required';
    isValid = false;
  }
  
  if (!formData.value.email.trim()) {
    validationErrors.value.email = 'Email is required';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    validationErrors.value.email = 'Invalid email format';
    isValid = false;
  }
  
  if (!formData.value.street.trim()) {
    validationErrors.value.street = 'Street address is required';
    isValid = false;
  }
  
  if (!formData.value.city.trim()) {
    validationErrors.value.city = 'City is required';
    isValid = false;
  }
  
  if (!formData.value.state.trim()) {
    validationErrors.value.state = 'State is required';
    isValid = false;
  }
  
  if (!formData.value.postal_code.trim()) {
    validationErrors.value.postal_code = 'ZIP / Postal code is required';
    isValid = false;
  }
  
  if (!formData.value.payment_method) {
    validationErrors.value.payment_method = 'Please select a payment method';
    isValid = false;
  }

  if (!formData.value.ageVerified) {
    validationErrors.value.ageVerified = 'You must confirm you are at least 21 years old to continue.';
    isValid = false;
  }
  
  return isValid;
};

// Form submission function
const submitForm = async () => {
  errorMessage.value = '';
  successMessage.value = '';
  
  if (!validateForm()) {
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    const response = await axios.post('/api/checkout/process', formData.value);
    
    if (response.data.success) {
      successMessage.value = response.data.message || 'Order successfully placed!';
      
      // Проверяем, если это крипто-платеж с URL для перенаправления
      if (response.data.payment_type === 'crypto' && response.data.redirect_url) {
        successMessage.value = 'Redirecting to payment page...';
        
        // Перенаправляем на страницу оплаты NOWPayments
        setTimeout(() => {
          window.location.href = response.data.redirect_url;
        }, 1000);
      } else {
        // Стандартное перенаправление на страницу подтверждения
        const redirectUrl = response.data.redirect_url || `/orders/${response.data.order_id}/confirmation`;
        
        setTimeout(() => {
          window.location.href = redirectUrl;
        }, 1500);
      }
    }
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response.data.errors || {};
    } else {
      errorMessage.value = err.response?.data?.message || 'An error occurred while processing your order. Please try again.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

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
    
    // Используем тот же API-эндпоинт, что и в CartPage.vue
    const response = await axios.get('/api/cart');
    console.log('Ответ API:', response.data);
    
    // Проверяем наличие данных в ответе
    if (response.data && response.data.items) {
      cartItems.value = response.data.items;
      console.log('Данные корзины загружены:', cartItems.value);
    } else {
      cartItems.value = [];
      console.log('Корзина пуста или не найдена:', response.data);
    }
  } catch (err) {
    error.value = 'Failed to load cart data. Please try again later.';
    console.error('Ошибка при загрузке данных корзины:', err);
  } finally {
    isLoading.value = false;
  }
};

// Запускаем загрузку корзины при монтировании компонента
onMounted(() => {
  fetchCart();
});
</script>

<template>
  <div class="w-full max-w-7xl mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Checkout</h1>
    
    <!-- Сообщения об ошибках/успехе -->
    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline">{{ successMessage }}</span>
      <button @click="successMessage = ''" class="absolute top-0 right-0 px-4 py-3">&times;</button>
    </div>
    
    <div v-if="errorMessage && !Object.keys(validationErrors).length" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span class="block sm:inline">{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="absolute top-0 right-0 px-4 py-3">&times;</button>
    </div>
    
    <!-- Загрузка -->
    <div v-if="isLoading" class="text-center py-8">
      <p class="text-gray-600">Loading checkout...</p>
    </div>
    
    <!-- Ошибка -->
    <div v-else-if="error" class="text-center py-8">
      <p class="text-red-600 mb-2">{{ error }}</p>
      <a href="/cart" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">Return to Cart</a>
    </div>
    
    <!-- Пустая корзина -->
    <div v-else-if="cartItems.length === 0" class="text-center py-8">
      <p class="text-gray-600 mb-4">Your cart is empty</p>
      <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">Continue Shopping</a>
    </div>
    
    <!-- Форма оформления заказа -->
    <div v-else class="flex flex-col lg:flex-row gap-8">
      <!-- Левая колонка: Форма доставки -->
      <div class="lg:w-2/3 lg:pr-8 mb-8 lg:mb-0">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
          
          <div class="mb-6">
            <h3 class="text-lg font-medium">Shipping Address</h3>
          </div>
          
          <!-- Первый ряд: Email и Телефон -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <input 
                type="email" 
                id="email" 
                v-model="formData.email" 
                class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{'border-red-500': validationErrors.email, 'border-gray-300': !validationErrors.email}"
                placeholder="Email"
                required
              >
              <p v-if="validationErrors.email" class="text-red-500 text-sm mt-1">{{ validationErrors.email }}</p>
            </div>
            <div>
              <input 
                type="tel" 
                id="phone" 
                v-model="formData.phone" 
                class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Phone (optional)"
              >
            </div>
          </div>
          
          <!-- Второй ряд: Имя и Компания -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <input 
                type="text" 
                id="name" 
                v-model="formData.name" 
                class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{'border-red-500': validationErrors.name, 'border-gray-300': !validationErrors.name}"
                placeholder="Name"
                required
              >
              <p v-if="validationErrors.name" class="text-red-500 text-sm mt-1">{{ validationErrors.name }}</p>
            </div>
            <div>
              <input 
                type="text" 
                id="company" 
                v-model="formData.company" 
                class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Company (optional)"
              >
            </div>
          </div>
          
          <!-- Третий ряд: Адрес -->
          <div class="mb-4">
            <input 
              type="text" 
              id="street" 
              v-model="formData.street" 
              placeholder="123 Main St"
              :class="{'border-red-500': validationErrors.street, 'border-gray-300': !validationErrors.street}"
              class="mt-1 block w-full rounded-md border px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            />
            <p v-if="validationErrors.street" class="text-red-500 text-sm mt-1">{{ validationErrors.street }}</p>
          </div>
          
          <!-- Четвертый ряд: Апартаменты/Номер квартиры -->
          <div class="mb-4">
            <input 
              type="text" 
              id="house" 
              v-model="formData.house" 
              placeholder="Apt, Suite, Unit, etc. (optional)"
              class="mt-1 block w-full rounded-md border px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          
          <!-- Пятый ряд: Город, Регион, Индекс -->
          <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
              <input 
                type="text" 
                id="city" 
                v-model="formData.city" 
                class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{'border-red-500': validationErrors.city, 'border-gray-300': !validationErrors.city}"
                placeholder="City"
                required
              >
              <p v-if="validationErrors.city" class="text-red-500 text-sm mt-1">{{ validationErrors.city }}</p>
            </div>
            <div>
              <select 
                id="state" 
                v-model="formData.state" 
                class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{'border-red-500': validationErrors.state, 'border-gray-300': !validationErrors.state}"
                required
              >
                <option value="" disabled>Select a state</option>
                <option value="Alabama">Alabama</option>
                <option value="Alaska">Alaska</option>
                <option value="Arkansas">Arkansas</option>
                <option value="California">California</option>
                <option value="Colorado">Colorado</option>
                <option value="Connecticut">Connecticut</option>
                <option value="Delaware">Delaware</option>
                <option value="Florida">Florida</option>
                <option value="Georgia">Georgia</option>
                <option value="Hawaii">Hawaii</option>
                <option value="Idaho">Idaho</option>
                <option value="Illinois">Illinois</option>
                <option value="Indiana">Indiana</option>
                <option value="Iowa">Iowa</option>
                <option value="Kansas">Kansas</option>
                <option value="Kentucky">Kentucky</option>
                <option value="Louisiana">Louisiana</option>
                <option value="Maine">Maine</option>
                <option value="Maryland">Maryland</option>
                <option value="Massachusetts">Massachusetts</option>
                <option value="Michigan">Michigan</option>
                <option value="Minnesota">Minnesota</option>
                <option value="Mississippi">Mississippi</option>
                <option value="Missouri">Missouri</option>
                <option value="Montana">Montana</option>
                <option value="Nebraska">Nebraska</option>
                <option value="Nevada">Nevada</option>
                <option value="New Hampshire">New Hampshire</option>
                <option value="New Jersey">New Jersey</option>
                <option value="New Mexico">New Mexico</option>
                <option value="New York">New York</option>
                <option value="North Carolina">North Carolina</option>
                <option value="North Dakota">North Dakota</option>
                <option value="Ohio">Ohio</option>
                <option value="Oklahoma">Oklahoma</option>
                <option value="Oregon">Oregon</option>
                <option value="Pennsylvania">Pennsylvania</option>
                <option value="Rhode Island">Rhode Island</option>
                <option value="South Carolina">South Carolina</option>
                <option value="South Dakota">South Dakota</option>
                <option value="Tennessee">Tennessee</option>
                <option value="Texas">Texas</option>
                <option value="Utah">Utah</option>
                <option value="Vermont">Vermont</option>
                <option value="Virginia">Virginia</option>
                <option value="Washington">Washington</option>
                <option value="West Virginia">West Virginia</option>
                <option value="Wisconsin">Wisconsin</option>
                <option value="Wyoming">Wyoming</option>
              </select>
              <p v-if="validationErrors.state" class="text-red-500 text-sm mt-1">{{ validationErrors.state }}</p>
            </div>
            <div>
              <input 
                type="text" 
                id="postal_code" 
                v-model="formData.postal_code" 
                placeholder="00000"
                :class="{'border-red-500': validationErrors.postal_code, 'border-gray-300': !validationErrors.postal_code}"
                class="mt-1 block w-full rounded-md border px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
              />
              <p v-if="validationErrors.postal_code" class="text-red-500 text-sm mt-1">{{ validationErrors.postal_code }}</p>
            </div>
          </div>
          
          <!-- Шестой ряд: Страна -->
          <div class="mb-6">
            <select 
              id="country" 
              v-model="formData.country" 
              class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="United States">United States</option>
            </select>
          </div>
          
          <!-- Комментарий к заказу -->
          <div class="mb-4">
            <label for="comment" class="block text-gray-700 mb-2">Order notes (optional)</label>
            <textarea 
              id="comment" 
              v-model="formData.comment" 
              class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
              placeholder="Additional information for the courier"
            ></textarea>
          </div>
        </div>
      </div>
      
      <!-- Правая колонка: Информация о заказе -->
      <div class="lg:w-1/3">
        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
          <h2 class="text-xl font-semibold mb-4">Your Order</h2>
          
          <div class="mb-4">
            <div class="max-h-48 overflow-y-auto mb-4">
              <div v-for="item in cartItems" :key="item.id" class="flex items-center mb-3 pb-3 border-b border-gray-100">
                <img :src="item.product.image_url" :alt="item.product.name" class="w-12 h-12 object-cover rounded mr-3">
                <div class="flex-1">
                  <p class="font-medium">{{ item.product.name }}</p>
                  <p class="text-sm text-gray-500">{{ item.quantity }} x {{ item.price.toFixed(2) }} $</p>
                </div>
                <div class="font-medium">
                  {{ (item.price * item.quantity).toFixed(2) }} $
                </div>
              </div>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
              <div class="flex justify-between mb-2">
                <span>Subtotal:</span>
                <span>{{ subtotal.toFixed(2) }} $</span>
              </div>
              
              <div class="flex justify-between font-semibold text-lg mt-4">
                <span>Total:</span>
                <span>{{ subtotal.toFixed(2) }} $</span>
              </div>
            </div>
          </div>
          
          <!-- Payment method selection with enhanced UI -->
          <div class="mb-4">
            <h3 class="text-lg font-medium mb-2">Payment Method</h3>
            <div class="transition-all duration-300 ease-in-out" :class="{'bg-green-50 border border-green-200 rounded-lg p-3': formData.payment_method, 'bg-yellow-50 border border-yellow-200 rounded-lg p-3': !formData.payment_method || validationErrors.payment_method}">
              <div class="space-y-3">
                <!-- Zelle payment option -->
                <label class="flex items-center p-2 cursor-pointer rounded-md" :class="{'bg-green-100': formData.payment_method === 'zelle'}">
                  <input 
                    type="radio" 
                    name="payment_method" 
                    value="zelle"
                    v-model="formData.payment_method"
                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 transition-all duration-200 ease-in-out"
                  >
                  <div class="ml-3">
                    <span class="font-medium" :class="{'text-green-700': formData.payment_method === 'zelle'}">Zelle</span>
                    <p class="text-sm text-gray-500">Instant bank transfers using Zelle service</p>
                  </div>
                </label>
                
                <!-- Crypto payment option -->
                <label class="flex items-center p-2 cursor-pointer rounded-md" :class="{'bg-green-100': formData.payment_method === 'crypto'}">
                  <input 
                    type="radio" 
                    name="payment_method" 
                    value="crypto"
                    v-model="formData.payment_method"
                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 transition-all duration-200 ease-in-out"
                  >
                  <div class="ml-3">
                    <span class="font-medium" :class="{'text-green-700': formData.payment_method === 'crypto'}">Cryptocurrency</span>
                    <p class="text-sm text-gray-500">Pay using Bitcoin, Ethereum or other cryptocurrencies</p>
                  </div>
                </label>
              </div>
              
              <p v-if="validationErrors.payment_method" class="text-red-500 text-sm mt-2">{{ validationErrors.payment_method }}</p>
            </div>
          </div>
          
          <!-- Age verification checkbox with enhanced interaction -->
          <div class="mb-4 transition-all duration-300 ease-in-out" :class="{'bg-green-50 border border-green-200 rounded-lg p-3': formData.ageVerified, 'bg-yellow-50 border border-yellow-200 rounded-lg p-3': !formData.ageVerified}">
            <label class="flex items-start cursor-pointer">
              <input 
                type="checkbox" 
                v-model="formData.ageVerified"
                class="mt-1 mr-2 h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-all duration-200 ease-in-out"
                id="age-verification"
              >
              <span class="text-sm" :class="{'text-green-700 font-medium': formData.ageVerified, 'text-gray-700': !formData.ageVerified}">
                I confirm that I am at least 21 years of age or older and legally allowed to purchase the products in this order.
              </span>
            </label>
            <p v-if="validationErrors.ageVerified" class="text-red-500 text-sm mt-2 ml-7">{{ validationErrors.ageVerified }}</p>
          </div>
          
          <!-- Order button with conditional states -->
          <button 
            @click.prevent="submitForm" 
            :disabled="isSubmitting || !formData.ageVerified || !formData.payment_method" 
            class="relative block w-full text-center py-3 rounded-md font-medium transition-all duration-300 ease-in-out overflow-hidden"
            :class="{
              'bg-blue-600 hover:bg-blue-700 text-white': formData.ageVerified && formData.payment_method && !isSubmitting,
              'bg-gray-400 cursor-not-allowed text-white': (!formData.ageVerified || !formData.payment_method) && !isSubmitting,
              'bg-blue-600 cursor-wait text-white opacity-80': isSubmitting
            }"
          >
            <div class="flex items-center justify-center space-x-2">
              <!-- Loading spinner for submission -->
              <svg v-if="isSubmitting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              
              <!-- Dynamic button text -->
              <span v-if="isSubmitting">Processing order...</span>
              <span v-else-if="!formData.payment_method">Select payment method</span>
              <span v-else-if="!formData.ageVerified">Age verification required</span>
              <span v-else>Place Order</span>
            </div>
            
            <!-- Progress bar animation for submissions -->
            <div v-if="isSubmitting" class="absolute bottom-0 left-0 h-1 bg-blue-400 animate-pulse" style="width: 100%;"></div>
          </button>
          
          <!-- Helper text based on what's missing -->
          <p v-if="!formData.payment_method" class="text-gray-500 text-xs text-center mt-2 animate-pulse">
            Please select a payment method to continue
          </p>
          <p v-else-if="!formData.ageVerified" class="text-gray-500 text-xs text-center mt-2 animate-pulse">
            Please check the box above to confirm your age before proceeding
          </p>
          
          <a href="/cart" class="block text-center mt-4 text-blue-600 hover:text-blue-800">
            Return to Cart
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
