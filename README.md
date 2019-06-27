#  Импортер за СУПТО "Фактуриране ЕУ" за OpenCart 3.x
Модулът предлага съвместимост на онлайн магазини базирани на платформата [OpenCart](https://www.opencart.com/) с [Наредба за изменение и допълнение на Наредба № Н-18](https://nap.bg/document?id=1265). Наредбата засяга тези онлайн магазини, които обработват плащания с наложен платеж  и кредитни карти в следствие на това, че според българското законодателство са задължени да издават фискален бон при обработка на онлайн поръчките. 

[![СУПТО Импортер за OpenCart 3](/upload/system/library/fakturirane/preview.jpg)](https://github.com/evtimov/oc3-supto-importer/releases/download/1.0.1/fakturiraneeu.ocmod.zip)

### Изисквания:
- Онлайн магазин базиран на OpenCart версия 3.x;
- Валиден лиценз за [СУПТО "Фактуриране ЕУ" с "облачна база данни" и "API функционалност"](https://fakturirane.eu/license/?supto=1&api=1&remote=1);
- Фискално устройство отговарящо на изискванията на Наредба Н-18 [Tremol](https://xn--n1abffd.com/%D1%81%D1%83%D0%BF%D1%82%D0%BE-%D1%83%D1%81%D1%82%D1%80%D0%BE%D0%B9%D1%81%D1%82%D0%B2%D0%B0/?provider_id=1), [Eltrade](https://xn--n1abffd.com/%D1%81%D1%83%D0%BF%D1%82%D0%BE-%D1%83%D1%81%D1%82%D1%80%D0%BE%D0%B9%D1%81%D1%82%D0%B2%D0%B0/?provider_id=3) или [Datecs](https://xn--n1abffd.com/%D1%81%D1%83%D0%BF%D1%82%D0%BE-%D1%83%D1%81%D1%82%D1%80%D0%BE%D0%B9%D1%81%D1%82%D0%B2%D0%B0/);
- Допълнителния [модул "Склад"](https://fakturirane.eu/skladova-programa/) в СУПТО "Фактуриране ЕУ" не е съвместим ако работите с опции на продукти.


### Инсталиране:  
1) Изтеглете файла __fakturiraneeu.ocmod.zip__ от [GitHub](https://github.com/evtimov/oc3-supto-importer/releases/download/1.0.3/fakturiraneeu.ocmod.zip) или официалния сайт на [OpenCart](https://www.opencart.com/index.php?route=marketplace/download&extension_id=37065);
2) Влезте в административния панел на вашия онлайн магазин и изберете меню "Extensions" > "Installer";
3) Натиснете бутона "Upload" и посочете zip файла.

### Настройки:
В официалния сайт на СУПТО "Фактуриране ЕУ" са описани [индивидуалните настройки на Модула](https://fakturirane.eu/za-online-magazin/), включително:

- API настройки;
- Начини на плащане;
- Каталог;
- Експортиране на поръчки към СУПТО;
- Ръчно експортиране при прекъсване на интернет;
- Импортиране на поръчка в СУПТО;
- Допълнителна [API функционалност](https://fakturirane.eu/help/api/).

След като инсталирате и настроите този модул, направените поръчки в онлайн магазина автоматично се импортират в облачната база на СУПТО, след което можете да ги обработите чрез [СУПТО "Фактуриране ЕУ"](https://fakturirane.eu/supto/).

---
&copy; 2019 "ЛИЦЕНЗ" ЕООД  &nbsp;  | &nbsp; [Авторско право и лиценз](LICENSE.md)
