# Queue Worker для парсинга Copart

## Быстрый старт

### Запуск queue worker:
```bash
cd /home/vov/flipp-am
./start_queue_worker.sh
```

### Проверка статуса:
```bash
ps aux | grep "queue:work"
```

### Просмотр логов:
```bash
tail -f /tmp/queue_worker.log
```

### Остановка:
```bash
pkill -f "queue:work"
```

## Автозапуск при старте системы

Добавьте в `~/.bashrc` или запускайте вручную при старте работы:
```bash
/home/vov/flipp-am/start_queue_worker.sh
```

## Важно!

⚠️ **Queue worker должен быть запущен для импорта из Copart!**

Без него парсинг не будет выполняться и приложение покажет ошибку timeout.
