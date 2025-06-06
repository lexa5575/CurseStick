# Настройка NOWPayments для CruseStick

## Шаги настройки:

### 1. Добавьте в файл `.env` следующие переменные:

```env
# NOWPayments API Configuration
NOWPAYMENTS_API_KEY=your_api_key_here
NOWPAYMENTS_API_URL=https://api.nowpayments.io
NOWPAYMENTS_IPN_SECRET=your_ipn_secret_here
NOWPAYMENTS_SANDBOX=false
```

### 2. Получите API ключи:

1. Зарегистрируйтесь на https://nowpayments.io/
2. В личном кабинете перейдите в раздел API
3. Создайте API ключ
4. Создайте IPN Secret для безопасных webhook уведомлений

### 3. Настройте IPN Callback URL в NOWPayments:

В настройках NOWPayments укажите IPN Callback URL:
```
https://yourdomain.com/payment/ipn
```

## Как это работает:

1. **Покупатель выбирает "Cryptocurrency" на странице checkout**
2. **Система создает заказ со статусом "Ожидает оплаты"**
3. **Создается инвойс через NOWPayments API**
4. **Покупатель перенаправляется на страницу оплаты NOWPayments**
5. **После успешной оплаты:**
   - NOWPayments отправляет IPN уведомление
   - Статус заказа меняется на "Оплачен"
   - Отправляются email уведомления (стандартные шаблоны OrderConfirmation)
   - Покупатель видит страницу успешной оплаты

## Тестирование:

Для тестирования используйте sandbox окружение:
```env
NOWPAYMENTS_API_URL=https://api.sandbox.nowpayments.io
NOWPAYMENTS_SANDBOX=true
```

## Статусы платежей:

- `waiting` - Ожидает оплаты
- `confirming` - Подтверждается в блокчейне
- `confirmed` - Подтверждено
- `finished` - Завершено (деньги получены)
- `failed` - Не удалось
- `refunded` - Возвращено
- `expired` - Истекло 