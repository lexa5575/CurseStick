/**
 * CruseStick Carousel - Alpine.js компонент для слайдера баннеров
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('carousel', () => ({
        // Данные карусели
        current: 0,
        items: [],
        autoplayInterval: null,
        
        // Инициализация карусели
        init() {
            // Если баннеров нет, добавляем демо-баннеры
            if (this.items.length === 0) {
                this.items = [
                    {
                        image_url: '/images/banners/banner1.jpg',
                        text: 'Лучшие товары для дома и интерьера'
                    },
                    {
                        image_url: '/images/banners/banner2.jpg',
                        text: 'Скидки до 50% на сезонные товары'
                    },
                    {
                        image_url: '/images/banners/banner3.jpg',
                        text: 'Бесплатная доставка при заказе от 5000 руб.'
                    }
                ];
            }
            
            // Запускаем автопрокрутку
            this.startAutoplay();
            
            // Останавливаем автопрокрутку при выходе со страницы
            window.addEventListener('beforeunload', () => {
                this.stopAutoplay();
            });
        },
        
        // Следующий слайд
        next() {
            this.current = (this.current + 1) % this.items.length;
            this.restartAutoplay();
        },
        
        // Предыдущий слайд
        prev() {
            this.current = (this.current - 1 + this.items.length) % this.items.length;
            this.restartAutoplay();
        },
        
        // Запуск автопрокрутки
        startAutoplay() {
            this.stopAutoplay(); // Сначала остановим, если уже запущен
            this.autoplayInterval = setInterval(() => {
                this.next();
            }, 5000); // 5 секунд между слайдами
        },
        
        // Остановка автопрокрутки
        stopAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }
        },
        
        // Перезапуск автопрокрутки (после ручного переключения)
        restartAutoplay() {
            this.stopAutoplay();
            this.startAutoplay();
        }
    }));
});
