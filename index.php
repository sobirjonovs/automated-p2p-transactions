<?php

use App\Payme\Api\Api;

require_once "vendor/autoload.php";

/**
 * 1-bosqich
 * Payme'ga o'zingizning telefon raqamingiz va parolingiz bilan kirib olishingiz kerak.
 * Telefon raqam 901234565 formatda bo'lishi kerak
 * */
$payme = new Api();
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam', 'password' => 'paymedagi parol']);
$payme->login()->sendActivationCode();

/**
 * 2-bosqich
 * Telefoningizga kelgan kodni tasdiqlaysiz va kod avtomatik ravishda
 * joriy so'rovni ishonilgan deb topadi
 *
 * 3-bosqich
 * Tasdiqlangan so'rovdan endi kerakli ma'lumotlarni o'zingizni bazangizga yozib olishingiz
 * kerak. Eng muhim ma'lumot bu device. Keyingisi bu kartaning ID raqami.
 * device ID raqami $payme->getDevice() orqali olinadi. Karta raqamlar esa
 * $payme->getMyCards() metod bilan olinadi.
 * */
$payme = new Api();
$payme->activate('telefonga kelgan kod')->registerDevice();
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam', 'password' => 'paymedagi parol']);
$device_id = $payme->getDevice(); // !!! MA'LUMOTLAR BAZAGA TEGISHLI TARTIBDA SAQLANSIN
$cards = $payme->getMyCards(); // !!! MA'LUMOTLAR BAZAGA TEGISHLI TARTIBDA SAQLANSIN | ID raqam massivning "_id" indeksida bo'ladi

/**
 * 4-bosqich
 * Payme kabinetingizdagi hamma cheklarni yoki ma'lum bir kartaga oid bo'lgan cheklarni ko'rish
 * */
$payme = new Api();
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam (Telefon raqam 901234565 formatda bo`lishi kerak)', 'password' => 'paymedagi parol']);
$cheques = $payme->getAllCheques(); // bugungi barcha tranzaksiyalarni olib beradi
$card_cheques = $payme->selectCard('karta ID raqami')->getCheques(); // Tegishli kartadagi tranzaksiyalarni olib beradi

/**
 * Tranzaksiyalar orasidan ma'lum izohdagi to'lovni topish
 *
 * Buning uchun findByComment(izoh, summa) metodi ishlatiladi. To'lov topilsa to'lovni ma'lumotlarini, aks holda false qaytaradi
 * Summa miqdori 100 ga ko'paytirilgan holda yozilsin
 * */
$payme = new Api();
$payme->setCredentials(['login' => 'paymega ulangan telefon raqam (Telefon raqam 901234565 formatda bo`lishi kerak)', 'password' => 'paymedagi parol']);

// Bitta kartadagi tranzaksiyalar orasidan izoh orqali qidirish
$payme->selectCard('karta id raqami')->findByComment('izoh', 0); // 0 ni o'rniga kerakli summa yozilsin

// Barcha kartalardagi tranzaksiyalar orasidan izoh orqali qidirish
$payme->cheques()->findByComment('izoh', 0);

