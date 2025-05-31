<!-- Баннеры-слайдер -->
<div class="container mx-auto px-4 py-8">
    <div 
        x-data="carousel()" 
        x-init="items = {{ json_encode($banners ?? []) }}; init()"
        class="relative mb-12 rounded-lg overflow-hidden shadow-xl"
        style="height: 400px;">
        
        <!-- Слайды -->
        <template x-for="(banner, index) in items" :key="index">
            <div 
                x-show="current === index"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="banner-slide h-full"
            >
                <img :src="banner.image_url" :alt="banner.text" class="w-full h-full object-cover">
                <div class="absolute inset-0" :class="banner.overlay_color || 'bg-black/40'" :style="{'background-color': !banner.overlay_color?.includes('bg-') ? banner.overlay_color : ''}">
                    <div class="flex flex-col items-center justify-center h-full p-8" :class="banner.text_alignment || 'text-center'">
                        <!-- Заголовок баннера -->
                        <h2 x-text="banner.text" 
                            :class="[
                                banner.text_size || 'text-4xl', 
                                banner.text_weight || 'font-bold', 
                                banner.text_shadow || 'shadow-none',
                                'mb-4'
                            ]" 
                            :style="{'color': banner.text_color || '#FFFFFF'}"></h2>
                        
                        <!-- Подзаголовок -->
                        <p x-text="banner.subtitle" x-show="banner.subtitle" class="text-white text-lg md:text-xl max-w-2xl mb-6"></p>
                        
                        <!-- Кнопка -->
                        <a :href="banner.url || '{{ route('categories.index') }}'" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-block">
                            <span x-text="banner.button_text || 'Смотреть товары'"></span>
                        </a>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- Навигация по слайдам -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
            <!-- Статически заданные 3 индикатора -->
            <button 
                @click="current = 0" 
                :class="current === 0 ? 'bg-white' : 'bg-white/50'"
                class="w-3 h-3 rounded-full transition-colors"
                aria-label="Перейти к слайду 1"
            ></button>
            <button 
                @click="current = 1" 
                :class="current === 1 ? 'bg-white' : 'bg-white/50'"
                class="w-3 h-3 rounded-full transition-colors"
                aria-label="Перейти к слайду 2"
            ></button>
            <button 
                @click="current = 2" 
                :class="current === 2 ? 'bg-white' : 'bg-white/50'"
                class="w-3 h-3 rounded-full transition-colors"
                aria-label="Перейти к слайду 3"
            ></button>
        </div>
        
        <!-- Стрелки навигации -->
        <button 
            @click="prev()" 
            class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 transition-colors"
            aria-label="Предыдущий слайд"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button 
            @click="next()" 
            class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 transition-colors"
            aria-label="Следующий слайд"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>

<!-- Подключение компонента карусели -->
<script src="{{ asset('js/carousel.js') }}"></script>
