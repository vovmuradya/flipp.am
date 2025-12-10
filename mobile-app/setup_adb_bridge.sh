#!/bin/bash
echo "🔧 Настройка ADB bridge между WSL и Windows..."
echo ""

# Устанавливаем ADB в WSL
echo "1. Установка ADB в WSL..."
sudo apt-get update -qq
sudo apt-get install -y android-tools-adb

echo ""
echo "2. Настройка подключения..."
# Находим IP Windows хоста
WINDOWS_IP=$(ip route | grep default | awk '{print $3}')
echo "   Windows IP: $WINDOWS_IP"

# Настраиваем переменную окружения
echo "export ADB_SERVER_SOCKET=tcp:$WINDOWS_IP:5037" >> ~/.bashrc
export ADB_SERVER_SOCKET=tcp:$WINDOWS_IP:5037

echo ""
echo "✅ Готово! Теперь выполни ЭТИ ШАГИ:"
echo ""
echo "═══════════════════════════════════════════════════════"
echo "В WINDOWS PowerShell (открой новое окно):"
echo "═══════════════════════════════════════════════════════"
echo "cd C:\Users\vov\AppData\Local\Android\Sdk\platform-tools"
echo ".\adb.exe kill-server"
echo ".\adb.exe -a nodaemon server"
echo ""
echo "(оставь это окно открытым!)"
echo ""
echo "═══════════════════════════════════════════════════════"
echo "Затем ЗДЕСЬ в WSL:"
echo "═══════════════════════════════════════════════════════"
echo "flutter devices    # Должен показать Pixel 7!"
echo "flutter run        # Запустит на эмуляторе!"
echo ""
