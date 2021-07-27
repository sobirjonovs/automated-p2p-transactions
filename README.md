[![\[Telegram\] tutorialsgroup](https://img.shields.io/badge/Telegram-Group-blue.svg?logo=telegram)](https://t.me/tutorialsgroup)
[![\[Telegram\] sobirjonovs](https://img.shields.io/badge/Telegram-blue.svg?logo=telegram)](https://t.me/sobirjonovs)  
## Bu qanaqa dastur?
p2p to'lovlarni avtomatlashtirish uchun dastur. Dastur **Payme** apidan foydalanadi.

## 1. Dastur ishlashi uchun talablar
- **>=** PHP v7.4
- **>=** Composer v1.10.1
## 2. O'rnatish
Dasturdan to'liq foydalanish uchun `composer` ni o'rnatishingiz kerak.  
```bash
$ composer install
```
## 3. Payme'ga ulanish
**Payme**'ga ulanish uchun biz `App\Payme\Api\Api` sinfidan foydalanamiz. Ushbu sinf orqali barcha **Payme** bilan bog'liq amaliyotlarni bajarishimiz mumkin. Keling, birinchi **Payme** kabinetimizga ushbu sinf orqali kiramiz:
```php
<?php
$payme = new Api();  
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam', 'password' => 'paymedagi parol']);  
$payme->login()->sendActivationCode();
```
`setCredentials([])` metodi orqali **Payme** akkauntimizning login parollarini belgilashimiz mumkin. Bu metodga **massiv** yuborish kerak. Massivda **login** va **parol** kalitlari bo'lishi shart. **Login** - bu sizning **Payme**dagi nomeringiz, nomer **901234564** formatda yozilishi kerak. **Parol** esa o'z holicha yoziladi. Ushbu ma'lumotlarni to'g'ri kiritganingizdan so'ng, sizga Paymedan telefon raqamingizga kod keladi. Ushbu kodni `activate` metodi orqali kiritasiz:
```php
<?php
$payme = new Api();  
$payme->activate('telefonga kelgan kod')->registerDevice();  
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam', 'password' => 'paymedagi parol']);  
$device_id = $payme->getDevice(); // !!! MA'LUMOTLAR BAZAGA TEGISHLI TARTIBDA SAQLANSIN  
$cards = $payme->getMyCards(); // !!! MA'LUMOTLAR BAZAGA TEGISHLI TARTIBDA SAQLANSIN | ID raqam massivning "_id" indeksida bo'ladi
```
Telefoningizga **Payme**dan *"**Payme API** qurilmasi ulandi"* degan SMS kelsa, demak hammasi muvaffaqiyatli bo'lgan. Shundan so'ng, kerakli ma'lumotlarni saqlab olishga navbat keladi. 
### Muhim ma'lumotlar
1. Device ID
2. Card ID

**Payme**ga API orqali kirilganda sessiya 15 daqiqa davom etadi va undan keyin API klyuch yangilanadi. Bu holatda yana qaytadan login qilmaslik uchun biz device id raqamini saqlab olishimiz kerak. Device id orqali aktiv sessiya klyuchni olishimiz mumkin (buni siz qilmaysiz, sessiya klyuchni dasturni o'zi topadi).  Cheklarni olayotganda, kartalarni olayotganda birinchi credentials'ni va device idni kiritish zarur. Device id `setDevice` metodi orqali kiritiladi.
```php
<?php
$payme = new Api();
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam (Telefon raqam 901234565 formatda bo`lishi kerak)', 'password' => 'paymedagi parol']);
$payme->setDevice('device id string');
```
Shundan so'ng bemalol amaliyotlarni kuzatish mumkin.
## Payme'dagi barcha tranzaksiyalarni ko'rish
Barcha tranzaksiyalarni ko'rish uchun `getAllCheques` yoki `cheques` metodlari ishlatiladi. Bu metodlar bir xil vazifa bajaradi faqat farqi `getAllCheques` chainable emas.
```php
<?php
$payme = new Api();  
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam (Telefon raqam 901234565 formatda bo`lishi kerak)', 'password' => 'paymedagi parol']); 
$payme->setDevice('device id string'); 
$cheques = $payme->getAllCheques(); 
// yoki
$cheques = $payme->cheques();
```
## Tranzaksiyalar orasidan tegishli izohli to'lovni topish
Agar siz P2P to'lovni avtomatlashtirmoqchi bo'lsangiz, sizga bu juda asqatadi. Izohli to'lovni tekshirish uchun `findByComment` metodi ham qo'shiladi. Namuna:
```php
<?php
$payme = new Api();  
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam (Telefon raqam 901234565 formatda bo`lishi kerak)', 'password' => 'paymedagi parol']);  
$payme->setDevice('device id string'); 

// Bitta kartadagi tranzaksiyalar orasidan izoh orqali qidirish  
$payme->selectCard('karta id raqami')->findByComment('izoh', 0); // 0 ni o'rniga kerakli summa yozilsin  
  
// Barcha kartalardagi tranzaksiyalar orasidan izoh orqali qidirish  
$payme->cheques()->findByComment('izoh', 0);
```
## Tranzaksiyalarni saralash
Tranzaksiyalarni saralash uchun `getAllCheques` yoki `cheques` metodlaridan biriga saralash parametrlaridan iborat massivni uzatishingiz kerak. Saralash parametrlari quyidagilardan iborat:

 1. **from**
Bu parametr 3 ta elementli massiv qabul qiladi. Elementlari `day, month, year` lar hisoblanadi. `month` ga qiymat berganda berilgan oydan -1 ayirish kerak. Ya'ni, 7-oydagi ma'lumotni topish uchun 6-oy deb yozishingiz kerak (g'alati, paymeda shunaqa ekan).
2. **count** 
Bu parametr `integer` qabul qiladi. Buning vazifasi nechta tranzaksiyani olib berishni hal qilish.
3. **card**
Bu parametr karta raqamning ID sini qabul qiladi. Buning vazifasi ko'rsatilgan karta raqamdagi tranzaksiyalarni olib berish.
4. **to** 
Bu parametr ham `from` bilan bir xil. Faqat bu ko'rsatilgan sanagacha bo'lgan tranzaksiyalarni olib berishni hal qiladi. 
5. **group**
Defolt qiymati `time`
6. **offset**
Defolt qiymati 0

## Namunalar
API dan foydalanish namunalar `index.php` faylida ham yozilgan.

## Eslatma!
Dastur va dastur uchun qo'llanma **Sanjarbek Sobirjonov** (telegram @sobirjonovs) tomonidan yozildi. Agar dasturda yoki qo'llanmada qandaydir xatolik topsangiz, **pull request** yoki murojaat qilishingizdan mamnun bo'lamiz. 
