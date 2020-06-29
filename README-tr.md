# pmChatBot
[Boting](https://github.com/Quiec/Boting) ile yazılmış, basit bir feed-back botu. Livegram bot'a benzer.

[🇹🇷 Türkçe](https://github.com/Quiec/pmChatBot/blob/master/README-tr.md) | [🇬🇧 English](https://github.com/Quiec/pmChatBot/blob/master/README.md) 
## Yükleme
### Basit Yöntem
[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

### Zor Yöntem
```sh
git clone https://github.com/Quiec/pmChatBot
cd pmChatBot
composer install
nano .env
# ÖRNEK ENV GİBİ EKLEME YAPIN #
php bot.php
```

## Örnek Env
```env
DB_TUR="json"
BOT_TOKEN="BOT TOKEN"
ADMIN_ID="ID'IN"
```

## Özellikleri
* Gizlilik ayarlarına uygun
* Heroku desteği
* JSON Dil Dosyası (Yazılar değişebilir)

## Komutlar
### /link
Kullanıcının (varsa) kullanıcı adını verir, ayrıca kullanıcıya ulaşılabilinecek bir link verir.

## İletişim
[Telegram Adresim](https://t.me/fusuf)