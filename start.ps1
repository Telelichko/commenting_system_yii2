Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd P:\Program_files\Redis; .\redis-server.exe"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd .\websocket-server; node server.js"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd .\yii2-comments; php -S localhost:8080 -t frontend/web"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd .\yii2-comments; php -S localhost:8081 -t backend/web"