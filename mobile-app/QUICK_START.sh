#!/bin/bash
echo "🔧 Установка ADB для подключения телефона..."
sudo apt-get update -qq
sudo apt-get install -y android-tools-adb android-tools-fastboot

echo ""
echo "✅ Готово! Теперь:"
echo ""
echo "1. Включи 'USB Debugging' на телефоне:"
echo "   Настройки → О телефоне → Тапай 7 раз на 'Номер сборки'"
echo "   Настройки → Для разработчиков → USB Debugging (Включи)"
echo ""
echo "2. Подключи телефон USB кабелем к компьютеру"
echo ""
echo "3. Запусти: flutter devices"
echo "   (должен появиться твой телефон)"
echo ""
echo "4. Запусти: flutter run"
echo ""
echo "Приложение установится на твой телефон! 📱"
