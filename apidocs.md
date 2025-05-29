# API Documentation - Sunny Lagoon

## Содержание
- [Введение](#введение)
- [Авторизация](#авторизация)
  - [Вход (Login)](#вход-login)
  - [Регистрация (Register)](#регистрация-register)
  - [Изменение пароля](#изменение-пароля)
- [Категории](#категории)
  - [Получение всех категорий](#получение-всех-категорий)
  - [Создание категории](#создание-категории)
  - [Обновление категории](#обновление-категории)
  - [Удаление категории](#удаление-категории)
- [Дома](#дома)
  - [Получение всех домов](#получение-всех-домов)
  - [Получение одного дома](#получение-одного-дома)
  - [Создание дома](#создание-дома)
  - [Обновление дома](#обновление-дома)
  - [Удаление дома](#удаление-дома)
- [Услуги](#услуги)
  - [Получение всех услуг](#получение-всех-услуг)
  - [Создание услуги](#создание-услуги)
  - [Обновление услуги](#обновление-услуги)
  - [Удаление услуги](#удаление-услуги)
- [Развлечения](#развлечения)
  - [Получение всех развлечений](#получение-всех-развлечений)
  - [Создание развлечения](#создание-развлечения)
  - [Обновление развлечения](#обновление-развлечения)
  - [Удаление развлечения](#удаление-развлечения)
- [Акции](#акции)
  - [Получение всех акций](#получение-всех-акций)
  - [Создание акции](#создание-акции)
  - [Обновление акции](#обновление-акции)
  - [Удаление акции](#удаление-акции)
- [Бронирования](#бронирования)
  - [Получение всех бронирований](#получение-всех-бронирований)
  - [Создание бронирования](#создание-бронирования)
  - [Обновление статуса бронирования](#обновление-статуса-бронирования)
- [Отзывы](#отзывы)
  - [Получение всех отзывов](#получение-всех-отзывов)
  - [Создание отзыва](#создание-отзыва)

## Введение

API базы отдыха "Sunny Lagoon" предоставляет доступ к основным функциям для управления контентом и обслуживания клиентов.

Базовый URL: `/backend/`

Форматы запросов:
- JSON: `Content-Type: application/json`
- Form-Data: `Content-Type: multipart/form-data` (для запросов с файлами)

Формат ответов:
```json
{
  "status": "success|error",
  "message": "Описание результата операции",
  "data": { ... } // Только при успешном выполнении
}
```

### Аутентификация

Для доступа к защищенным эндпоинтам необходимо использовать JWT-токен, который передается в заголовке Authorization:

```
Authorization: Bearer {token}
```

## Авторизация

### Вход (Login)

**Запрос:**
- Метод: `POST`
- URL: `/auth/login`
- Тело запроса (JSON):
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Авторизация успешна",
  "data": {
    "user": {
      "id_users": 1,
      "email": "user@example.com",
      "is_admin": 1
    },
    "token": "JWT-токен"
  }
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": "Неверный email или пароль"
}
```

### Регистрация (Register)

**Запрос:**
- Метод: `POST`
- URL: `/auth/register`
- Тело запроса (JSON):
```json
{
  "email": "newuser@example.com",
  "password": "password123"
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Регистрация успешна",
  "data": {
    "user": {
      "id_users": 2,
      "email": "newuser@example.com",
      "is_admin": 0
    },
    "token": "JWT-токен"
  }
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": "Пользователь с таким email уже существует"
}
```

### Изменение пароля

**Запрос:**
- Метод: `PUT`
- URL: `/auth/change-password`
- Заголовки: `Authorization: Bearer {token}`
- Тело запроса (JSON):
```json
{
  "current_password": "password123",
  "new_password": "newpassword123"
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Пароль успешно изменен",
  "data": {}
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": "Неверный текущий пароль"
}
```

## Категории

### Получение всех категорий

**Запрос:**
- Метод: `GET`
- URL: `/categories`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список категорий получен",
  "data": {
    "categories": [
      {
        "id_category": 1,
        "title": "Стандартный",
        "short_title": "дом",
        "short_title_mult": "дома",
        "capacity": 4,
        "description": "Комфортабельный дом для отдыха",
        "prev_text": "Краткое описание категории",
        "image_link": "category/standard.jpg"
      }
    ]
  }
}
```

### Создание категории

**Запрос:**
- Метод: `POST`
- URL: `/categories`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `title`: Название категории
  - `short_title`: Краткое название (ед. число)
  - `short_title_mult`: Краткое название (мн. число)
  - `capacity`: Вместимость
  - `description`: Полное описание
  - `prev_text`: Краткое описание
  - `image`: Файл изображения

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Категория успешно создана",
  "data": {
    "category": {
      "id_category": 2,
      "title": "Люкс",
      "short_title": "дом",
      "short_title_mult": "дома",
      "capacity": 6,
      "description": "Роскошный дом для отдыха с повышенным комфортом",
      "prev_text": "Краткое описание категории люкс",
      "image_link": "category/luxury.jpg"
    }
  }
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": {
    "title": ["Поле title обязательно для заполнения"]
  }
}
```

### Обновление категории

**Запрос:**
- Метод: `PUT`
- URL: `/categories/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `title`: Название категории
  - `short_title`: Краткое название (ед. число)
  - `short_title_mult`: Краткое название (мн. число)
  - `capacity`: Вместимость
  - `description`: Полное описание
  - `prev_text`: Краткое описание
  - `image`: Файл изображения (опционально)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Категория успешно обновлена",
  "data": {
    "category": {
      "id_category": 2,
      "title": "Люкс Премиум",
      "short_title": "дом",
      "short_title_mult": "дома",
      "capacity": 8,
      "description": "Обновленное описание",
      "prev_text": "Обновленное краткое описание",
      "image_link": "category/luxury_premium.jpg"
    }
  }
}
```

### Удаление категории

**Запрос:**
- Метод: `DELETE`
- URL: `/categories/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Категория успешно удалена",
  "data": {}
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": "Категория не найдена"
}
```

## Дома

### Получение всех домов

**Запрос:**
- Метод: `GET`
- URL: `/homes`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список домов получен",
  "data": {
    "homes": [
      {
        "id_home": 1,
        "id_category": 1,
        "title": "Дом у озера",
        "capacity": 4,
        "description": "Комфортабельный дом с видом на озеро",
        "price": 5000,
        "category_title": "Стандартный",
        "images": ["home/lake_house_1.jpg", "home/lake_house_2.jpg"]
      }
    ]
  }
}
```

### Получение одного дома

**Запрос:**
- Метод: `GET`
- URL: `/homes/{id}`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Дом получен",
  "data": {
    "home": {
      "id_home": 1,
      "id_category": 1,
      "title": "Дом у озера",
      "capacity": 4,
      "description": "Комфортабельный дом с видом на озеро",
      "price": 5000,
      "category_title": "Стандартный",
      "images": ["home/lake_house_1.jpg", "home/lake_house_2.jpg"]
    }
  }
}
```

### Создание дома

**Запрос:**
- Метод: `POST`
- URL: `/homes`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `id_category`: ID категории
  - `title`: Название дома
  - `capacity`: Вместимость
  - `description`: Описание
  - `price`: Цена за сутки
  - `images[]`: Файлы изображений (несколько)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Дом успешно создан",
  "data": {
    "home": {
      "id_home": 2,
      "id_category": 2,
      "title": "Люкс с сауной",
      "capacity": 6,
      "description": "Роскошный дом с сауной и террасой",
      "price": 8000,
      "category_title": "Люкс",
      "images": ["home/luxury_sauna_1.jpg", "home/luxury_sauna_2.jpg"]
    }
  }
}
```

### Обновление дома

**Запрос:**
- Метод: `PUT`
- URL: `/homes/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `id_category`: ID категории
  - `title`: Название дома
  - `capacity`: Вместимость
  - `description`: Описание
  - `price`: Цена за сутки
  - `update_images`: `true` если нужно обновить изображения
  - `images[]`: Файлы изображений (если update_images=true)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Дом успешно обновлен",
  "data": {
    "home": {
      "id_home": 2,
      "id_category": 2,
      "title": "Люкс с сауной и бассейном",
      "capacity": 8,
      "description": "Обновленное описание",
      "price": 10000,
      "category_title": "Люкс",
      "images": ["home/luxury_updated_1.jpg", "home/luxury_updated_2.jpg"]
    }
  }
}
```

### Удаление дома

**Запрос:**
- Метод: `DELETE`
- URL: `/homes/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Дом успешно удален",
  "data": {}
}
```

## Услуги

### Получение всех услуг

**Запрос:**
- Метод: `GET`
- URL: `/services`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список услуг получен",
  "data": {
    "services": [
      {
        "id_services": 1,
        "title": "Завтрак",
        "description": "Континентальный завтрак",
        "price": 500
      }
    ]
  }
}
```

### Создание услуги

**Запрос:**
- Метод: `POST`
- URL: `/services`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (JSON или form-data):
```json
{
  "title": "Ужин",
  "description": "Ужин из трех блюд",
  "price": 1200
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Услуга успешно создана",
  "data": {
    "service": {
      "id_services": 2,
      "title": "Ужин",
      "description": "Ужин из трех блюд",
      "price": 1200
    }
  }
}
```

### Обновление услуги

**Запрос:**
- Метод: `PUT`
- URL: `/services/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (JSON или form-data):
```json
{
  "title": "Ужин Люкс",
  "description": "Ужин из пяти блюд с вином",
  "price": 2500
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Услуга успешно обновлена",
  "data": {
    "service": {
      "id_services": 2,
      "title": "Ужин Люкс",
      "description": "Ужин из пяти блюд с вином",
      "price": 2500
    }
  }
}
```

### Удаление услуги

**Запрос:**
- Метод: `DELETE`
- URL: `/services/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Услуга успешно удалена",
  "data": {}
}
```

## Развлечения

### Получение всех развлечений

**Запрос:**
- Метод: `GET`
- URL: `/gaiety`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список развлечений получен",
  "data": {
    "gaieties": [
      {
        "id_gaiety": 1,
        "title": "Рыбалка",
        "description": "Рыбалка на озере с предоставлением снастей",
        "image_link": "gaiety/fishing.jpg"
      }
    ]
  }
}
```

### Создание развлечения

**Запрос:**
- Метод: `POST`
- URL: `/gaiety`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `title`: Название развлечения
  - `description`: Описание
  - `image`: Файл изображения

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Развлечение успешно создано",
  "data": {
    "gaiety": {
      "id_gaiety": 2,
      "title": "Катание на лодках",
      "description": "Аренда лодок для прогулок по озеру",
      "image_link": "gaiety/boating.jpg"
    }
  }
}
```

### Обновление развлечения

**Запрос:**
- Метод: `PUT`
- URL: `/gaiety/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `title`: Название развлечения
  - `description`: Описание
  - `image`: Файл изображения (опционально)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Развлечение успешно обновлено",
  "data": {
    "gaiety": {
      "id_gaiety": 2,
      "title": "Прогулки на катере",
      "description": "Аренда катеров с капитаном",
      "image_link": "gaiety/motorboat.jpg"
    }
  }
}
```

### Удаление развлечения

**Запрос:**
- Метод: `DELETE`
- URL: `/gaiety/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Развлечение успешно удалено",
  "data": {}
}
```

## Акции

### Получение всех акций

**Запрос:**
- Метод: `GET`
- URL: `/promotions`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список акций получен",
  "data": {
    "promotions": [
      {
        "id_promotion": 1,
        "title": "Раннее бронирование",
        "description": "Скидка 15% при бронировании за 30 дней",
        "image_link": "promotion/early_booking.jpg"
      }
    ]
  }
}
```

### Создание акции

**Запрос:**
- Метод: `POST`
- URL: `/promotions`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `title`: Название акции
  - `description`: Описание
  - `image`: Файл изображения

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Акция успешно создана",
  "data": {
    "promotion": {
      "id_promotion": 2,
      "title": "Длительное проживание",
      "description": "Скидка 20% при бронировании от 7 дней",
      "image_link": "promotion/long_stay.jpg"
    }
  }
}
```

### Обновление акции

**Запрос:**
- Метод: `PUT`
- URL: `/promotions/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (`multipart/form-data`):
  - `title`: Название акции
  - `description`: Описание
  - `image`: Файл изображения (опционально)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Акция успешно обновлена",
  "data": {
    "promotion": {
      "id_promotion": 2,
      "title": "Длительное проживание",
      "description": "Скидка 25% при бронировании от 7 дней",
      "image_link": "promotion/long_stay_updated.jpg"
    }
  }
}
```

### Удаление акции

**Запрос:**
- Метод: `DELETE`
- URL: `/promotions/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Акция успешно удалена",
  "data": {}
}
```

## Бронирования

### Получение всех бронирований

**Запрос:**
- Метод: `GET`
- URL: `/reservations`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список бронирований получен",
  "data": {
    "reservations": [
      {
        "id_reservation": 1,
        "id_home": 1,
        "name": "Иван Иванов",
        "phone": "+7(999)123-45-67",
        "date_enter": "2023-07-15",
        "date_back": "2023-07-20",
        "count_old": 2,
        "count_child": 1,
        "status": 1,
        "created_at": "2023-06-10 15:30:00",
        "home_title": "Дом у озера",
        "home_price": 5000
      }
    ]
  }
}
```

### Создание бронирования

**Запрос:**
- Метод: `POST`
- URL: `/reservations`
- Тело запроса (JSON или form-data):
```json
{
  "id_home": 1,
  "name": "Петр Петров",
  "phone": "+7(999)987-65-43",
  "date_enter": "2023-08-01",
  "date_back": "2023-08-05",
  "count_old": 2,
  "count_child": 2
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Бронирование успешно создано",
  "data": {
    "reservation": {
      "id_reservation": 2,
      "id_home": 1,
      "name": "Петр Петров",
      "phone": "+7(999)987-65-43",
      "date_enter": "2023-08-01",
      "date_back": "2023-08-05",
      "count_old": 2,
      "count_child": 2,
      "status": 0,
      "created_at": "2023-06-15 12:45:00",
      "home_title": "Дом у озера",
      "home_price": 5000
    }
  }
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": "Дом уже забронирован на выбранные даты"
}
```

### Обновление статуса бронирования

**Запрос:**
- Метод: `PUT`
- URL: `/reservations/{id}`
- Заголовки: `Authorization: Bearer {token}` (требуются права администратора)
- Тело запроса (JSON или form-data):
```json
{
  "status": 1
}
```

**Статусы бронирования:**
- 0: Новая
- 1: Подтверждена
- 2: Отменена

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Статус бронирования успешно обновлен",
  "data": {
    "reservation": {
      "id_reservation": 2,
      "id_home": 1,
      "name": "Петр Петров",
      "phone": "+7(999)987-65-43",
      "date_enter": "2023-08-01",
      "date_back": "2023-08-05",
      "count_old": 2,
      "count_child": 2,
      "status": 1,
      "created_at": "2023-06-15 12:45:00",
      "home_title": "Дом у озера",
      "home_price": 5000
    }
  }
}
```

## Отзывы

### Получение всех отзывов

**Запрос:**
- Метод: `GET`
- URL: `/reviews`

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Список отзывов получен",
  "data": {
    "reviews": [
      {
        "id_reviews": 1,
        "name": "Анна",
        "text": "Отличное место для отдыха! Обязательно приедем еще раз.",
        "created_at": "2023-05-20 10:15:00"
      }
    ]
  }
}
```

### Создание отзыва

**Запрос:**
- Метод: `POST`
- URL: `/reviews`
- Тело запроса (JSON или form-data):
```json
{
  "name": "Михаил",
  "text": "Прекрасное место для семейного отдыха. Чистый воздух и живописные виды."
}
```

**Успешный ответ:**
```json
{
  "status": "success",
  "message": "Отзыв успешно создан",
  "data": {
    "review": {
      "id_reviews": 2,
      "name": "Михаил",
      "text": "Прекрасное место для семейного отдыха. Чистый воздух и живописные виды.",
      "created_at": "2023-06-18 16:30:00"
    }
  }
}
```

**Ответ с ошибкой:**
```json
{
  "status": "error",
  "message": {
    "text": ["Поле text должно содержать минимум 10 символов"]
  }
}
``` 