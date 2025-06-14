<template>
  <transition 
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 transform -translate-y-2 scale-95"
    enter-to-class="opacity-100 transform translate-y-0 scale-100"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 transform translate-y-0 scale-100"
    leave-to-class="opacity-0 transform -translate-y-2 scale-95"
  >
    <div v-if="message" class="error-message">
      <div class="error-content">
        <div class="error-icon">
          <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <p class="error-text">{{ message }}</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: ''
  }
});
</script>