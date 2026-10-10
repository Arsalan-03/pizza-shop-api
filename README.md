# Pizza Shop API

Backend API для интернет-магазина пиццы и напитков.

**Текущий статус:** Этап 1 — Docker-окружение (Symfony skeleton + Nginx + PostgreSQL).

---

## Стек технологий

| Слой            | Технология                              |
| --------------- | --------------------------------------- |
| Язык            | PHP 8.4                                 |
| Фреймворк       | Symfony 7.1                             |
| База данных     | PostgreSQL 16                           |
| Веб-сервер      | Nginx 1.27 (Alpine)                     |
| Контейнеризация | Docker, Docker Compose v2.24+           |
| Dev-инструменты | Xdebug 3.5 (только dev), Composer 2.10  |

---

## Требования

На хост-машине нужны только Docker и Docker Compose.

| Инструмент       | Минимальная версия | Зачем                                        |
| ---------------- | ------------------ | -------------------------------------------- |
| Docker Engine    | 24                 | Поддержка Compose v2                         |
| Docker Compose   | **2.24**           | `!reset` / `!override` в prod overrides      |
| GNU Make         | любая              | Удобная обёртка над командами (`make init`)  |

Проверка:

```bash
docker --version
docker compose version   # должно быть >= 2.24
make --version
```

> **Важно:** все зависимости приложения (PHP, Composer, расширения) живут внутри контейнеров.
> На хост-машине не нужно устанавливать ничего, кроме Docker.

---

## Быстрый старт

```bash
git clone git@github.com:Arsalan-03/pizza-shop-api.git
cd pizza-shop-api
make init
```

Команда `make init` выполняет полную первичную настройку:

1. Создаёт `.env` из `.env.example`.
2. Собирает dev-образы Docker.
3. Запускает контейнеры (PHP-FPM, Nginx, PostgreSQL).
4. Устанавливает Composer-зависимости внутри PHP-контейнера.
5. Печатает URL приложения.

После успешного завершения API доступно по адресу:

**http://localhost:8080**

> Порт можно изменить через `NGINX_PORT` в `.env`.

---

## Основные команды

| Команда                 | Что делает                                                  |
| ----------------------- | ----------------------------------------------------------- |
| `make help`             | Показать список всех команд                                 |
| `make init`             | Полная настройка (env + build + up + composer install)      |
| `make up`               | Запустить контейнеры (dev)                                  |
| `make down`             | Остановить контейнеры (dev)                                 |
| `make restart`          | Перезапустить контейнеры                                    |
| `make ps`               | Статус контейнеров                                          |
| `make logs`             | Следить за логами всех сервисов                             |
| `make sh`               | Зайти в PHP-контейнер по bash                               |
| `make sh-db`            | Зайти в `psql` внутри PostgreSQL-контейнера                 |
| `make console CMD="…"`  | Выполнить команду Symfony console                           |
| `make composer CMD="…"` | Выполнить команду Composer                                  |
| `make migrate`          | Применить Doctrine-миграции                                 |
| `make migrate-diff`     | Сгенерировать миграцию по изменениям сущностей              |
| `make cache-clear`      | Очистить кэш Symfony                                        |
| `make clean`            | Остановить контейнеры **и удалить volume** (⚠ данные БД)     |

---

## Production-режим

Prod использует отдельный Docker target (без Xdebug, без dev-зависимостей, код запечён в образ):

```bash
make up-prod      # собрать и запустить prod
make down-prod    # остановить
```

Под капотом выполняется:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yaml up -d --build
```

Отличия от dev-режима:

| Аспект                | Dev                              | Prod                                |
| --------------------- | -------------------------------- | ----------------------------------- |
| Docker target         | `dev`                            | `prod`                              |
| Исходный код          | bind-mount из `./app`            | запечён в образ                     |
| Xdebug                | включён                          | не установлен                       |
| Composer-зависимости  | dev + prod                       | только prod (`--no-dev`)            |
| Порт Postgres наружу  | проброшен (5432)                 | доступен только внутри Docker-сети  |
| Symfony env           | `dev`, `APP_DEBUG=1`             | `prod`, `APP_DEBUG=0`               |

---

## Структура проекта

```
.
├── app/                          # Symfony-приложение
│   ├── bin/
│   ├── config/
│   ├── public/                   # веб-корень (index.php)
│   ├── src/
│   ├── composer.json
│   └── .env                      # Symfony env (значения по умолчанию, коммитится)
├── docker/
│   ├── php/
│   │   ├── Dockerfile            # multistage: base → dev / prod
│   │   ├── php.ini
│   │   ├── php-fpm.conf          # глобальный конфиг FPM
│   │   ├── www.conf              # конфиг пула FPM
│   │   └── xdebug.ini            # только для dev
│   └── nginx/
│       └── default.conf
├── docker-compose.yml            # dev
├── docker-compose.prod.yaml      # prod overrides
├── .env.example                  # шаблон инфраструктурных env-переменных
├── .env                          # локальный инфра-env (НЕ коммитится)
├── Makefile
└── README.md
```

### Env-файлы

Два независимых набора переменных окружения:

| Файл                          | Область           | Коммитится?                       | Назначение                                        |
| ----------------------------- | ----------------- | --------------------------------- | ------------------------------------------------- |
| `.env` / `.env.example`       | Инфраструктура    | `.env` — нет, `.env.example` — да | Порты, креды Postgres, UID/GID                    |
| `app/.env` / `app/.env.local` | Symfony           | `.env` — да, `.env.local` — нет   | `APP_ENV`, `APP_SECRET`, `DATABASE_URL`           |

Реальные секреты хранятся в `.env` (инфраструктура) и `app/.env.local` (Symfony) — оба **не коммитятся**.

---

## База данных

PostgreSQL 16 работает в отдельном контейнере. Параметры подключения берутся из инфраструктурного `.env` и прокидываются в PHP-контейнер как `DATABASE_URL`.

Открыть `psql`-сессию:

```bash
make sh-db
```

Миграции (когда появятся маппинги сущностей на следующих этапах):

```bash
make migrate        # применить ожидающие миграции
make migrate-diff   # сгенерировать миграцию по изменениям сущностей
```

Данные сохраняются в named-volume `postgres_data` и выживают при `make down` / `make restart`. Чтобы стереть: `make clean` (⚠ удаляет volume).

---

## Решение проблем

### Порт уже занят

Если `8080` или `5432` заняты, открой `.env` и измени:

```dotenv
NGINX_PORT=8081
POSTGRES_PORT=5433
```

Затем `make restart`.

### Контейнеры не подхватывают изменения конфигов

PHP-образ запекает конфиги (`.ini`, FPM-конфиги). После их правки нужно **пересобрать образ**:

```bash
docker compose build php
docker compose up -d php
```

`make restart` **не поможет** — он перезапускает контейнер из старого образа.

### Xdebug не подключается (Linux)

Убедись, что IDE слушает порт `9003` и что `host.docker.internal` резолвится внутри контейнера. В этом проекте нужный `extra_hosts` уже настроен в `docker-compose.yml`.

### `docker compose` ругается на `!reset`

Обнови Compose до **v2.24 или новее**:

```bash
docker compose version
```

---

## Roadmap

- [x] **Этап 1** — Docker-окружение
- [ ] Этап 2 — Domain model и схема БД
- [ ] Этап 3 — REST API
- [ ] Этап 4 — Асинхронная обработка (RabbitMQ)
- [ ] Этап 5 — Кэширование (Redis)
- [ ] Этап 6 — Тесты
- [ ] Этап 7 — CI/CD (GitLab)