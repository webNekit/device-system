# Система Управления Ремонтом Устройств

## ERP-система "Новые Решения"

---

## 📋 Обзор Проекта

**Тип:** Бизнес-платформа для управления сервисными центрами по ремонту устройств  
**Архитектура:** Domain-Driven Design (DDD) на Laravel 13  
**Назначение:** Полное управление рабочим процессом от создания заявки клиентом до завершения ремонта и финансового учёта

---

## 🛠 Технологический Стек

### Бэкенд

- **Фреймворк:** Laravel 13.x
- **Версия PHP:** 8.3+ (промоуты свойств конструктора, типизированные свойства)
- **База Данных:** SQLite (настраивается для MySQL/PostgreSQL)
- **Тип Первичных Ключей:** ULID (Универсальный Лексикографически Сортируемый Идентификатор)

### Фронтенд

- **UI Фреймворк:** Livewire 4.2 (полноценные реактивные компоненты)
- **CSS Фреймворк:** TailwindCSS 4.0
- **Инструмент Сборки:** Vite 8.0
- **JavaScript:** Alpine.js (клиентские интеракции)

### Ключевые Пакеты

| Пакет                       | Версия | Назначение                                  |
| --------------------------- | ------ | ------------------------------------------- |
| `spatie/laravel-permission` | v7.2   | Ролевая модель доступа                      |
| `spatie/laravel-data`       | v4.20  | DTO и объекты передачи данных               |
| `laravel/pulse`             | v1.7   | Мониторинг производительности приложения    |
| `barryvdh/laravel-dompdf`   | v3.1   | Генерация PDF-счетов (русский язык)         |
| `laravel/boost`             | v2.0   | Инструменты Laravel Boost для AI-разработки |

---

## 🎯 Основные Функции

### 1. Управление Филиалами

- Поддержка нескольких филиалов/локаций с независимыми операциями
- **Изоляция Филиалов:** Пользователи не-админы видят только данные своего филиала
- Настройка часового пояса для каждого филиала
- Настройки филиала хранятся в JSON

### 2. Управление Взаимоотношениями с Клиентами (CRM)

- Типы клиентов: Физические и Юридические лица
- **Интеграция с DaData:** Автоматический поиск данных компании по ИНН
- Уровни лояльности с процентами скидок
- Отслеживание бонусного баланса
- Расширенный поиск и фильтрация

### 3. Управление Заявками/Заказами на Ремонт

- Полный жизненный цикл заявки от приёмки до выдачи
- **Канбан-доска:** Визуальное управление рабочим процессом с drag-and-drop
- **Система Пайплайнов:** Настраиваемые рабочие процессы с этапами
- **SLA-трекинг:** Отслеживание дедлайнов по этапам с автоматическими расчётами
- Уровни приоритета: низкий, нормальный, срочный
- Информация об устройстве: тип, бренд, модель, серийный номер
- Описание дефекта и примерная стоимость

### 4. Система Управления Складом (WMS)

- Поддержка нескольких складов на филиал
- **Ячейки Хранения:** Адресация стеллаж-полка-ячейка
- Каталог товаров с управлением SKU
- Учёт товарных единиц с отслеживанием серийных номеров
- Статусы запасов: доступно, зарезервировано, сломано, в пути
- История складских операций
- Уведомления о минимальном пороге запасов

### 5. Управление Запчастями на Заявках

- Прикрепление складских запчастей к заявкам на ремонт
- Отслеживание цены продажи и гарантийных дней для каждой запчасти
- Автоматическое изменение статуса на "зарезервировано" при прикреплении к заявке
- Автоматическое обновление расчёта стоимости

### 6. Система Чек-листов

- Чек-листы на основе шаблонов для разных этапов
- Этапные чек-листы: приёмка, диагностика, контроль качества, выдача
- Чек-листы для типов устройств
- Несколько типов полей: чекбокс, текст, выбор
- Результаты хранятся в JSON для каждой заявки

### 7. Клиентский Портал

- **Магические Ссылки:** Доступ клиентов без логина/регистрации
- Просмотр статуса ремонта
  -Workflow согласования/отклонения стоимости
- Автоматические переходы этапов при ответе клиента
- Безопасность на основе токенов с истечением срока

### 8. Финансовый Менеджмент (P&L Дашборд)

- Учёт доходов и расходов
- Способы оплаты: наличные, карта, перевод
- Транзакции по категориям
- **Метрики:** Выручка, Себестоимость, Валовая Прибыль, Чистая Прибыль, Маржа %
- Фильтрация по периодам: этот месяц, прошлый месяц, всё время
- Фильтрация финансов по филиалам

### 9. Справочник Устройств

- Иерархический каталог: Тип → Бренд → Модель
- Импорт CSV с автоопределением кодировки (UTF-8, Windows-1251 и др.)
- Интеграция с сервисом GSMArena для спецификаций устройств
- Кэширование спецификаций для уменьшения API-вызовов

### 10. Генерация PDF-Счетов

- Русскоязычный "Акт выполненных работ"
- Автоматическая генерация при завершении заявки
- Включает использованные запчасти с информацией о гарантии
- Использует шрифт DejaVu Sans для поддержки кириллицы

---

## 🗄 Структура Базы Данных

### Основные Таблицы

#### `users` (Пользователи)

```
- id: ULID (первичный ключ)
- name: string (имя)
- email: string (уникальный)
- password: string (хешированный)
- branch_id: ULID (внешний ключ → branches)
- is_active: boolean
- Роли: Админ, Менеджер Филиала, Техник, Кладовщик
```

#### `branches` (Филиалы)

```
- id: ULID (первичный ключ)
- name: string (название)
- address: string (адрес)
- timezone: string (часовой пояс)
- settings: JSON (настройки)
```

### Домен Клиентов

#### `loyalty_levels` (Уровни Лояльности)

```
- id: ULID (первичный ключ)
- name: string (название)
- discount_percent: integer (процент скидки)
```

#### `customers` (Клиенты)

```
- id: ULID (первичный ключ)
- type: enum (individual/legal - физ/юр лицо)
- name: string (имя/название)
- inn: string (ИНН для юр лиц)
- phone: string (телефон)
- email: string (email)
- loyalty_level_id: ULID (внешний ключ)
- bonus_balance: decimal (бонусный баланс)
- meta: JSON (дополнительные данные)
```

#### `dadata_cache` (Кэш DaData)

```
- inn: string (первичный ключ)
- company_name: string (название компании)
- cached_at: timestamp (время кэширования)
```

### Домен Склада/WMS

#### `warehouses` (Склады)

```
- id: ULID (первичный ключ)
- branch_id: ULID (внешний ключ → branches)
- name: string (название)
```

#### `storage_locations` (Ячейки Хранения)

```
- id: ULID (первичный ключ)
- warehouse_id: ULID (внешний ключ → warehouses)
- rack: string (стеллаж)
- shelf: string (полка)
- bin: string (ячейка)
```

#### `product_categories` (Категории Товаров)

```
- id: ULID (первичный ключ)
- parent_id: ULID (самоссылка для иерархии)
- name: string (название)
```

#### `products` (Товары)

```
- id: ULID (первичный ключ)
- category_id: ULID (внешний ключ → product_categories)
- sku: string (уникальный артикул)
- name: string (название)
- purchase_price: decimal (закупочная цена)
```

#### `inventory_items` (Товарные Единицы)

```
- id: ULID (первичный ключ)
- product_id: ULID (внешний ключ → products)
- storage_location_id: ULID (внешний ключ → storage_locations)
- serial_number: string (серийный номер, nullable)
- status: enum (available, reserved, broken, in_transit)
- purchase_price: decimal (закупочная цена)
```

#### `inventory_transactions` (Складские Операции)

```
- id: ULID (первичный ключ)
- inventory_item_id: ULID (внешний ключ → inventory_items)
- from_location_id: ULID (откуда, nullable)
- to_location_id: ULID (куда, nullable)
- quantity: integer (количество)
- type: enum (in, out, transfer, adjustment)
- user_id: ULID (внешний ключ → users)
- notes: string (примечания, nullable)
```

### Домен Заявок

#### `pipelines` (Пайплайны)

```
- id: ULID (первичный ключ)
- name: string (название)
- is_default: boolean (пайплайн по умолчанию)
```

#### `pipeline_stages` (Этапы Пайплайна)

```
- id: ULID (первичный ключ)
- pipeline_id: ULID (внешний ключ → pipelines)
- name: string (название этапа)
- order_column: integer (порядок)
- sla_max_minutes: integer (макс. минуты SLA, nullable)
```

#### `tickets` (Заявки)

```
- id: ULID (первичный ключ)
- ulid: string (уникальный, индексированный)
- branch_id: ULID (внешний ключ → branches)
- customer_id: ULID (внешний ключ → customers)
- pipeline_id: ULID (внешний ключ → pipelines)
- current_stage_id: ULID (внешний ключ → pipeline_stages)
- assigned_technician_id: ULID (внешний ключ → users, nullable)
- device_type: string (тип устройства)
- device_brand: string (бренд)
- device_model: string (модель)
- serial_number: string (серийный номер, nullable)
- defect_description: text (описание дефекта)
- estimated_cost: decimal (примерная стоимость, nullable)
- priority: enum (low, normal, urgent)
- status: enum (open, in_progress, waiting_customer, completed, cancelled)
- sla_deadline_at: timestamp (дедлайн SLA, nullable)
- completed_at: timestamp (время завершения, nullable)
```

#### `ticket_stage_histories` (История Этапов Заявок)

```
- id: ULID (первичный ключ)
- ticket_id: ULID (внешний ключ → tickets)
- stage_id: ULID (внешний ключ → pipeline_stages)
- user_id: ULID (внешний ключ → users)
- entered_at: timestamp (время входа)
- exited_at: timestamp (время выхода, nullable)
- duration_minutes: integer (длительность в минутах, расчётное)
```

#### `ticket_comments` (Комментарии к Заявкам)

```
- id: ULID (первичный ключ)
- ticket_id: ULID (внешний ключ → tickets)
- user_id: ULID (внешний ключ → users)
- comment: text (текст комментария)
- is_internal: boolean (внутренний/видимый клиенту)
```

#### `ticket_inventory` (Запчасти на Заявках)

```
- id: ULID (первичный ключ)
- ticket_id: ULID (внешний ключ → tickets)
- inventory_item_id: ULID (внешний ключ → inventory_items)
- selling_price: decimal (цена продажи)
- warranty_days: integer (гарантийные дни)
```

#### `checklists` (Чек-листы)

```
- id: ULID (первичный ключ)
- stage_id: ULID (внешний ключ → pipeline_stages, nullable)
- device_type_id: ULID (внешний ключ → device_types, nullable)
- name: string (название)
- type: enum (intake, qc, output)
- is_active: boolean (активен)
```

#### `checklist_items` (Элементы Чек-листов)

```
- id: ULID (первичный ключ)
- checklist_id: ULID (внешний ключ → checklists)
- question: string (вопрос)
- field_type: enum (checkbox, text, select)
- options: JSON (опции для select, nullable)
- is_required: boolean (обязательный)
- order_column: integer (порядок)
```

#### `checklist_results` (Результаты Чек-листов)

```
- id: ULID (первичный ключ)
- ticket_id: ULID (внешний ключ → tickets)
- checklist_id: ULID (внешний ключ → checklists)
- user_id: ULID (внешний ключ → users)
- answers_json: JSON (ответы)
- completed_at: timestamp (время завершения)
```

#### `checklist_templates` (Шаблоны Чек-листов)

```
- id: ULID (первичный ключ)
- stage_id: ULID (внешний ключ → pipeline_stages, nullable)
- device_type_id: ULID (внешний ключ → device_types, nullable)
- name: string (название)
- type: enum (intake, qc, output)
```

#### `checklist_template_items` (Элементы Шаблонов Чек-листов)

```
- id: ULID (первичный ключ)
- template_id: ULID (внешний ключ → checklist_templates)
- question: string (вопрос)
- field_type: enum (checkbox, text, select)
- options: JSON (опции, nullable)
- is_required: boolean (обязательный)
- order_column: integer (порядок)
```

#### `magic_links` (Магические Ссылки)

```
- id: ULID (первичный ключ)
- ticket_id: ULID (внешний ключ → tickets)
- token: string (уникальный, индексированный)
- expires_at: timestamp (истекает)
- last_visited_at: timestamp (последнее посещение, nullable)
```

### Домен Справочника Устройств

#### `device_types` (Типы Устройств)

```
- id: ULID (первичный ключ)
- name: string (название)
- slug: string (уникальный слаг)
```

#### `device_brands` (Бренды Устройств)

```
- id: ULID (первичный ключ)
- type_id: ULID (внешний ключ → device_types)
- name: string (название)
```

#### `device_models` (Модели Устройств)

```
- id: ULID (первичный ключ)
- brand_id: ULID (внешний ключ → device_brands)
- name: string (название)
```

#### `device_specs` (Спецификации Устройств)

```
- id: ULID (первичный ключ)
- brand: string (бренд)
- model_name: string (модель)
- specs_json: JSON (спецификации)
- cached_at: timestamp (время кэширования)
```

### Финансовый Домен

#### `financial_transactions` (Финансовые Транзакции)

```
- id: ULID (первичный ключ)
- branch_id: ULID (внешний ключ → branches)
- user_id: ULID (внешний ключ → users)
- ticket_id: ULID (внешний ключ → tickets, nullable)
- type: enum (income, expense)
- category: string (категория)
- payment_method: enum (cash, card, transfer)
- amount: decimal (сумма)
- description: text (описание, nullable)
- transaction_date: timestamp (дата транзакции)
```

### Системные Таблицы

- `cache`, `cache_locks` - Кэширование Laravel
- `sessions` - Пользовательские сессии
- `jobs`, `job_batches`, `failed_jobs` - Система очередей
- `pulse_*` - Данные мониторинга Laravel Pulse
- `model_has_roles`, `role_has_permissions` - Разрешения Spatie

---

## 🌐 Маршруты и Эндпоинты

Все маршруты веб-базированные (без REST API). Требуется аутентификация, если не указано иное.

| Метод | Маршрут                          | Описание                              | Доступ                          |
| ----- | -------------------------------- | ------------------------------------- | ------------------------------- |
| GET   | `/login`                         | Страница входа                        | Публично                        |
| POST  | `/login`                         | Обработка входа                       | Публично                        |
| GET   | `/status/{token}`                | Клиентский портал (магическая ссылка) | Публично (по токену)            |
| POST  | `/logout`                        | Выход                                 | Аутентифицирован                |
| GET   | `/`                              | Корневой редирект на дашборд          | Аутентифицирован                |
| GET   | `/dashboard`                     | Дашборд администратора с метриками    | Админ, Менеджер Филиала         |
| GET   | `/branches`                      | Управление филиалами                  | Админ, Менеджер Филиала         |
| GET   | `/users`                         | Управление пользователями             | Админ, Менеджер Филиала         |
| GET   | `/customers`                     | Список клиентов с фильтрами           | Админ, Менеджер Филиала         |
| GET   | `/customers/{customer:ulid}`     | Детальная информация о клиенте        | Админ, Менеджер Филиала         |
| GET   | `/finance`                       | Финансовый P&L дашборд                | Админ, Менеджер Филиала         |
| GET   | `/settings/devices`              | Управление справочником устройств     | Админ, Менеджер Филиала         |
| GET   | `/settings/checklists`           | Шаблоны чек-листов                    | Админ, Менеджер Филиала         |
| GET   | `/inventory`                     | Управление складом                    | Админ, Кладовщик                |
| GET   | `/inventory/products`            | Управление каталогом товаров          | Админ, Кладовщик                |
| GET   | `/tickets`                       | Канбан-доска заявок                   | Админ, Менеджер Филиала, Техник |
| GET   | `/tickets/{ticket:ulid}`         | Детальная информация о заявке         | Админ, Менеджер Филиала, Техник |
| GET   | `/tickets/{ticket:ulid}/invoice` | Генерация PDF-счета                   | Админ, Менеджер Филиала, Техник |

---

## 🔗 Отношения Моделей

```
User (ULID)
├── belongsTo: Branch (филиал)
├── hasMany: Ticket (как назначенный техник)
├── hasMany: TicketComment (комментарии)
├── hasMany: FinancialTransaction (транзакции)
└── hasRoles: Админ, Менеджер Филиала, Техник, Кладовщик

Branch (Филиал)
├── hasMany: User (пользователи)
├── hasMany: Warehouse (склады)
├── hasMany: Ticket (заявки)
└── hasMany: FinancialTransaction (транзакции)

Customer (Клиент)
├── belongsTo: LoyaltyLevel (уровень лояльности)
├── hasMany: Ticket (заявки)
└── meta: JSON (дополнительные данные)

Ticket (Заявка)
├── belongsTo: Branch (филиал)
├── belongsTo: Customer (клиент)
├── belongsTo: Pipeline (пайплайн)
├── belongsTo: PipelineStage (текущий этап)
├── belongsTo: User (назначенный техник)
├── hasMany: TicketComment (комментарии)
├── hasMany: TicketInventory (использованные запчасти)
├── hasMany: FinancialTransaction (транзакции)
├── hasMany: TicketStageHistory (история этапов)
└── hasMany: MagicLink (магические ссылки)

Pipeline (Пайплайн)
└── hasMany: PipelineStage (этапы, упорядоченные)

PipelineStage (Этап Пайплайна)
├── belongsTo: Pipeline (пайплайн)
├── hasMany: Ticket (заявки)
├── hasMany: Checklist (чек-листы)
└── hasMany: ChecklistTemplate (шаблоны чек-листов)

Warehouse (Склад)
├── belongsTo: Branch (филиал)
└── hasMany: StorageLocation (ячейки хранения)

StorageLocation (Ячейка Хранения)
├── belongsTo: Warehouse (склад)
└── hasMany: InventoryItem (товарные единицы)

Product (Товар)
├── belongsTo: ProductCategory (категория)
└── hasMany: InventoryItem (товарные единицы)

InventoryItem (Товарная Единица)
├── belongsTo: Product (товар)
├── belongsTo: StorageLocation (ячейка хранения)
├── hasMany: InventoryTransaction (складские операции)
└── hasMany: TicketInventory (запчасти на заявках)

FinancialTransaction (Финансовая Транзакция)
├── belongsTo: Branch (филиал)
├── belongsTo: User (пользователь)
└── belongsTo: Ticket (заявка, опционально)
```

---

## 🔌 Сторонние Интеграции

### 1. DaData API (Сервис Данных Российских Компаний)

**Назначение:** Автоматический поиск данных компании по ИНН  
**Конфигурация:** `config/services.php`, переменная окружения `DADATA_TOKEN`  
**Возможности:**

- Слой кэширования с 30-дневным сроком хранения
- Уменьшает ручной ввод данных для юридических лиц
- Возвращает название компании, адрес, юридический статус

### 2. GSMArena Service (Спецификации Устройств)

**Назначение:** Автоматическое получение спецификаций устройств  
**Реализация:** Mock-сервис с кэшированием  
**Кэшируемые Данные:**

- Спецификации дисплея (размер, тип, разрешение)
- Чипсет, CPU, GPU
- Ёмкость и тип батареи
- Спецификации камеры
- Память и RAM

### 3. Laravel Pulse

**Назначение:** Мониторинг производительности приложения  
**Дашборд:** `/pulse` (доступно админам)  
**Отслеживаемые Метрики:**

- Взаимодействия с кэшем (попадания, промахи, записи)
- Исключения и ошибки
- Производительность очередей
- Медленные запросы
- Медленные запросы HTTP
- Пользовательские сессии

### 4. DomPDF (barryvdh/laravel-dompdf)

**Назначение:** Генерация PDF для счетов  
**Возможности:**

- Поддержка русского языка (шрифт DejaVu Sans)
- Автоматическая генерация счетов при завершении заявки
- Включает данные компании, использованные запчасти, информацию о гарантии

---

## 🎨 Архитектура Фронтенда

### Livewire Компоненты

Все интерактивные UI-компоненты на базе Livewire:

- Валидация форм в реальном времени с wire:model
- Модальные диалоги для создания/редактирования
- Drag-and-drop для перемещения заявок по канбану
- Встроенный поиск и фильтрация
- Пагинация с wire:click
- Состояния загрузки с wire:loading

### Конфигурация TailwindCSS

- CSS-фреймворк на основе утилит
- Кастомная тема в `tailwind.config.js`
- Адаптивные макеты (mobile-first)
- Поддержка тёмного режима (если настроено)

### Использование Alpine.js

- Лёгкие клиентские интеракции
- Выпадающие меню и модальные окна
- Клиентская валидация форм
- Анимации и переходы

### Система Сборки Vite

- Сборка и минификация ассетов
- Hot Module Replacement (HMR) для разработки
- Автоматическое версионирование для cache busting
- Команды:
    - `npm run dev` - Разработка с HMR
    - `npm run build` - Продакшен сборка

---

## ⚙️ Специальная Бизнес-Логика

### 1. Область Изоляции Филиалов

**Файл:** `app/Application/Scopes/BranchIsolationScope.php`

```php
// Автоматически фильтрует все запросы по филиалу пользователя
// Админы видят все данные, другие пользователи - только свой филиал
class BranchIsolationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (! auth()->user()->hasRole('Admin')) {
            $builder->where($model->getTable().'.branch_id', auth()->user()->branch_id);
        }
    }
}
```

**Применяется к:** Ticket, Customer, Warehouse, FinancialTransaction и др.

### 2. SLA-Трекинг

- Каждый этап пайплайна может иметь `sla_max_minutes`
- Заявки автоматически получают `sla_deadline_at` на основе SLA первого этапа
- История этапов отслеживает `duration_minutes` для каждого перехода
- Визуальные индикаторы для приближающихся/просроченных дедлайнов

### 3. Блокировка Перехода Этапов

- Этап "Согласование с клиентом" блокирует ручное продвижение
- Клиент должен одобрить/отклонить через портал магической ссылки
- Одобрение клиента автоматически продвигает заявку на следующий этап
- Предотвращает обход согласия клиента техниками

### 4. Финансовая Автоматизация

- Закрытие заявки (генерация счёта) автоматически создаёт транзакцию дохода
- Себестоимость рассчитывается из закупочных цен использованных запчастей
- Выручка - Себестоимость = Валовая Прибыль
- Валовая Прибыль - Расходы = Чистая Прибыль
- P&L дашборд показывает рентабельность в реальном времени

### 5. Система Бронирования Запчастей

- Запчасти, прикреплённые к заявкам, меняют статус на "зарезервировано"
- Зарезервированные запчасти нельзя прикрепить к нескольким заявкам
- Нельзя удалить ячейки хранения с доступными товарами
- Транзакции запасов отслеживают все перемещения

### 6. Автоматизация Чек-листов

- Чек-листы автоматически появляются на основе:
    - Текущего этапа пайплайна
    - Типа устройства
    - Типа чек-листа (приёмка/контроль качества/выдача)
- Результаты хранятся в JSON для гибкости
- Шаблоны позволяют переиспользовать определения чек-листов

### 7. Безопасность Магических Ссылок

- Токен-базированный доступ для клиентов (без пароля)
- Отслеживание срока действия (`expires_at`)
- Временная метка последнего посещения для аналитики
- Принудительное использование HTTPS (продакшен)

---

## 👥 Роли Пользователей и Разрешения

### Иерархия Ролей (Spatie Laravel Permission)

| Роль                 | Уровень Доступа                          | Область     |
| -------------------- | ---------------------------------------- | ----------- |
| **Админ**            | Полный доступ ко всей системе            | Все филиалы |
| **Менеджер Филиала** | Полный доступ в пределах филиала         | Один филиал |
| **Техник**           | Управление заявками, чек-листы, запчасти | Один филиал |
| **Кладовщик**        | Только склад и управление запасами       | Один филиал |

### Примеры Разрешений

- `view tickets` - Просмотр списка и деталей заявок
- `create tickets` - Создание новых заявок на ремонт
- `update tickets` - Редактирование существующих заявок
- `manage inventory` - Полный доступ к складу
- `manage users` - Управление пользователями (только Админ)
- `manage branches` - Настройка филиалов (только Админ)
- `view finances` - Доступ к P&L дашборду

---

## 🏗 Архитектура Приложения

### Структура Domain-Driven Design (DDD)

```
app/
├── Domain/                    # Бизнес-логика по доменам
│   ├── Branch/
│   │   ├── Actions/           # CreateBranchAction, UpdateBranchAction
│   │   ├── Models/            # Модель Branch
│   │   └── Livewire/          # Компоненты управления филиалами
│   ├── Customer/
│   │   ├── Actions/           # CreateCustomerAction, SyncCustomerAction
│   │   ├── Models/            # Customer, LoyaltyLevel
│   │   ├── Livewire/          # Компоненты клиентов
│   │   └── Services/          # Интеграция DaData
│   ├── Finance/
│   │   ├── Actions/           # CreateTransactionAction
│   │   ├── Models/            # FinancialTransaction
│   │   └── Livewire/          # Финансовый дашборд
│   ├── Inventory/
│   │   ├── Actions/           # CreateProductAction, AttachPartToTicketAction
│   │   ├── Models/            # Product, InventoryItem, Warehouse
│   │   └── Livewire/          # Управление складом
│   └── Ticketing/
│       ├── Actions/           # CreateTicketAction, UpdateTicketStageAction
│       ├── Models/            # Ticket, Pipeline, Checklist
│       └── Livewire/          # Канбан заявок, Детали заявки
│
├── Application/               # Общие сервисы приложения
│   ├── Models/                # DaDataCache (общий)
│   ├── Scopes/                # BranchIsolationScope
│   └── Services/              # DaDataService, GSMArenaService
│
├── Presentation/              # UI слой
│   └── Livewire/              # Общие Livewire компоненты
│
├── Http/
│   ├── Controllers/           # Традиционные контроллеры (минимальные)
│   └── Middleware/            # Кастомные middleware
│
└── Models/                    # Пусто (модели в доменах)
```

### Паттерн Action

Бизнес-логика инкапсулирована в Action-классах:

- Единственная ответственность
- Типизированные параметры
- Типизированные результаты
- Легко тестировать

**Примеры:**

- `CreateTicketAction::execute(CreateTicketData $data): Ticket`
- `AttachPartToTicketAction::execute(Ticket $ticket, InventoryItem $item, array $options)`
- `CreateTransactionAction::execute(array $data): FinancialTransaction`

### Паттерн DTO (Spatie Laravel Data)

Объекты передачи данных для типобезопасности:

- `CreateTicketData` - Данные для создания заявки
- `BranchData` - Данные филиала
- `CustomerData` - Данные клиента
- `FinancialTransactionData` - Данные транзакции

---

## 🧪 Тестирование

### Фреймворк Тестирования

- **PHPUnit v12** - Основной фреймворк тестирования
- **Feature Tests** - Интеграционные тесты на уровне HTTP
- **Unit Tests** - Изолированное модульное тестирование

### Команды Тестирования

```bash
# Запустить все тесты
php artisan test --compact

# Запустить тесты конкретного файла
php artisan test --compact tests/Feature/TicketTest.php

# Запустить тесты по фильтру имени
php artisan test --compact --filter=testTicketCreation
```

### Паттерн Factory

Фабрики моделей для генерации тестовых данных:

- Кастомные состояния (например, `->reserved()`, `->completed()`)
- Определение отношений
- Последовательности для связанных моделей

---

## 📝 Конвенции Кода

### Стандарты PHP

- Промоуты свойств конструктора PHP 8
- Явные объявления типов возврата
- Типы для всех параметров методов
- Фигурные скобки для всех управляющих конструкций
- TitleCase для ключей Enum
- PHPDoc блоки вместо inline комментариев
- Определения формы массива в PHPDoc

### Конвенции Laravel

- Команды `php artisan make:` для скаффолдинга
- Eloquent API Resources для API (если используется)
- Именованные маршруты с хелпером `route()`
- Фабрики и сидеры для тестовых данных
- Form requests для валидации

### Конвенции Livewire

- Управление состоянием на стороне сервера
- Валидация в actions (wire:model)
- Авторизация в методах компонентов
- Состояния загрузки с wire:loading

### Форматирование Кода

- **Laravel Pint** - Форматтер кода

```bash
vendor/bin/pint --dirty --format agent
```

---

## 🔐 Функции Безопасности

### Аутентификация

- Сессионная аутентификация Laravel
- Хеширование паролей с bcrypt
- Функция "Запомнить меня"
- Rate limiting попыток входа

### Авторизация

- Ролевой контроль доступа (Spatie)
- Область изоляции филиалов
- Классы политик для авторизации моделей
- Middleware для защиты маршрутов

### Защита Данных

- CSRF защита на всех формах
- Предотвращение XSS через экранирование Blade
- Предотвращение SQL-инъекций через Eloquent
- ULID первичные ключи (непоследовательные, неудагаемые)

### Безопасность Магических Ссылок

- Криптографически безопасные токены
- Временные метки истечения
- Отслеживание одноразового использования (опционально)
- Принудительное использование HTTPS (продакшен)

---

## 📊 Мониторинг и Наблюдаемость

### Дашборд Laravel Pulse (`/pulse`)

- **Кэш:** Соотношения попаданий/промахов, медленные запросы
- **Исключения:** Отслеживание ошибок с трассировкой стека
- **Очереди:** Пропускная способность задач, неудачные задачи
- **Медленные Запросы:** Обнаружение N+1, время запросов
- **Медленные Запросы HTTP:** Производительность HTTP-запросов
- **Использование:** Активность пользователей, использование функций

### Логирование

- Каналы логов Laravel (stack, single, daily)
- Уровни логов: debug, info, notice, warning, error, critical, alert, emergency
- Расположение логов: `storage/logs/laravel.log`

---

## 🚀 Развёртывание и Разработка

### Настройка Окружения

```bash
# Установка зависимостей
composer install
npm install

# Копирование файла окружения
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate

# Запуск миграций
php artisan migrate --seed

# Сборка ассетов
npm run build

# Сервер разработки
composer run dev  # или npm run dev
```

### Переменные Окружения (.env)

```
APP_NAME="ERP Система Ремонта Устройств"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
# или DB_CONNECTION=mysql с учётными данными

DADATA_TOKEN=your_dadata_token

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

### Artisan Команды

```bash
# Список всех маршрутов
php artisan route:list

# Просмотр конфигурации
php artisan config:show app.name

# Очистка кэшей
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# База данных
php artisan migrate
php artisan migrate:rollback
php artisan db:seed

# Воркеры очередей (если используются асинхронные очереди)
php artisan queue:work
php artisan pulse:check
```

---

## 📦 Справочник Ключевых Файлов

| Файл                   | Назначение                 |
| ---------------------- | -------------------------- |
| `composer.json`        | PHP зависимости            |
| `package.json`         | Node.js зависимости        |
| `vite.config.js`       | Конфигурация сборщика Vite |
| `tailwind.config.js`   | Тема TailwindCSS           |
| `bootstrap/app.php`    | Бутстрап приложения        |
| `routes/web.php`       | Определение веб-маршрутов  |
| `app/Providers/*.php`  | Сервис-провайдеры          |
| `config/*.php`         | Файлы конфигурации         |
| `database/migrations/` | Миграции базы данных       |
| `database/seeders/`    | Сидеры базы данных         |
| `resources/views/`     | Blade шаблоны              |
| `resources/js/`        | JavaScript ассеты          |
| `resources/css/`       | CSS ассеты                 |
| `public/`              | Публичные ассеты           |
| `storage/`             | Логи, кэш, загрузки        |
| `tests/`               | PHPUnit тесты              |

---

## 🎯 Сводка Бизнес-Процессов

### Типичный Жизненный Цикл Заявки

1. **Приёмка:** Клиент приносит устройство → Создание заявки → Заполнение чек-листа приёмки
2. **Диагностика:** Техник диагностирует → Оценка стоимости → Прикрепление запчастей
3. **Согласование с Клиентом:** Отправка магической ссылки → Клиент одобряет/отклоняет стоимость
4. **Ремонт:** Техник ремонтирует устройство → Обновление статуса → Чек-лист контроля качества
5. **Контроль Качества:** Чек-лист QC → Финальное тестирование
6. **Завершение:** Генерация счёта → Оплата → Чек-лист выдачи → Выдача устройства

### Финансовый Поток

- **Доходы:** Оплата заявки (наличные/карта/перевод)
- **Себестоимость:** Использованные запчасти (закупочная цена)
- **Валовая Прибыль:** Доходы - Себестоимость
- **Расходы:** Операционные расходы (аренда, коммунальные услуги, зарплаты)
- **Чистая Прибыль:** Валовая Прибыль - Расходы

### Поток Запасов

- **Поступление:** Заказ на закупку → Добавление на склад → Статус "доступно"
- **Бронирование:** Прикрепление к заявке → Статус "зарезервировано"
- **Использование:** Завершение заявки → Списание из запасов
- **Перемещение:** Перемещение между локациями → Транзакция записывается
- **Корректировка:** Коррекция количества → Корректирующая транзакция

---

## 📞 Поддержка и Документация

### Внутренняя Документация

- `README.md` - Обзор проекта и настройка
- `AGENTS.md` - Гайдлайны Laravel Boost для AI-ассистентов
- `GEMINI.md` - Дополнительные гайдлайны для AI-ассистентов

### Внешние Ресурсы

- Документация Laravel: https://laravel.com/docs
- Документация Livewire: https://livewire.laravel.com
- Документация TailwindCSS: https://tailwindcss.com/docs
- Spatie Laravel Permission: https://spatie.be/docs/laravel-permission
- Spatie Laravel Data: https://spatie.be/docs/laravel-data

---

**Последнее Обновление:** Апрель 2026  
**Версия:** 2.0  
**Команда Разработки:** ERP-система "Новые Решения"
