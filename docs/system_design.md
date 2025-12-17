# System ewidencji myjni autobusowej i samochodowej

## 1. Architektura i warstwy
System został zaprojektowany jako aplikacja webowa PHP 8.x + MySQL/MariaDB, zgodna z prostym MVC i podziałem na warstwy:
- **Baza danych** – relacyjna, z pełnymi kluczami obcymi, indeksami i historią zmian.
- **Logika biznesowa** – serwisy domenowe (PHP) z walidacją, blokadami duplikatów, kontrolą uprawnień i obsługą błędów.
- **Warstwa prezentacji** – panel administracyjny (pełny dostęp) oraz panel operatora (interfejs dotykowy, minimalny zakres pól, brak finansów).
- **Moduł raportowy** – silnik zapytań i agregacji bez limitów zakresu, z eksportem CSV/XLSX/PDF oraz trybem raw data.
- **Integracje i automatyzacja** – punkt startowy pod CRON (zamykanie zmian, generowanie raportów, backupy) i API/rozszerzenia (RFID, kamery/OCR).

## 2. Model danych (MySQL)
Poniżej propozycja schematu z relacjami, indeksami i kluczami obcymi. Typy: INT=INT UNSIGNED, PK=PRIMARY KEY, FK=FOREIGN KEY, NN=NOT NULL, UQ=UNIQUE, IDX=INDEX.

### 2.1 Firmy i klienci
- `companies` (PK `id`, `name` NN, `type` ENUM('firma','indywidualny'), `tax_id`, `address`, `city`, `zip`, `country`, `email`, `phone`, `billing_notes`, `active` BOOL default 1, `created_at`, `updated_at`)
- `company_contracts` (PK `id`, FK `company_id`→`companies.id`, `name`, `start_date`, `end_date` nullable, `status` ENUM('aktywny','wygasły','wypowiedziany'), `notes`, `created_at`, `updated_at`)
- `company_pricelists` (PK `id`, FK `company_id`, `valid_from` NN, `valid_to` nullable, `status` ENUM('aktywny','archiwalny'), `created_at`, `updated_at`)

### 2.2 Flota pojazdów
- `vehicle_types` (PK `id`, `name` NN, `category` ENUM('autobus','ciezarowy','osobowy'), `description`)
- `vehicles` (PK `id`, `registration_number` NN UQ, `fleet_number` UQ nullable, FK `company_id`→`companies.id`, FK `vehicle_type_id`→`vehicle_types.id`, `year` SMALLINT, `active` BOOL, `notes`, `created_at`, `updated_at`)
- Indeksy: IDX `company_id`, IDX `vehicle_type_id`, UQ `registration_number` dla szybkiego wyszukiwania.

### 2.3 Pracownicy i zmiany
- `users` (PK `id`, `login` UQ, `password_hash`, `name`, `role` ENUM('admin','manager','operator','raporty'), `active` BOOL, `created_at`, `updated_at`)
- `shifts` (PK `id`, `name`, `start_time`, `end_time`, `active` BOOL)
- `user_shifts` (PK `id`, FK `user_id`→`users.id`, FK `shift_id`→`shifts.id`, `valid_from`, `valid_to` nullable)

### 2.4 Cenniki i pozycje cenowe
- `prices` (PK `id`, `name`, `scope` ENUM('global','firmowy'), FK `company_pricelist_id` nullable, `valid_from`, `valid_to` nullable, `status` ENUM('aktywny','archiwalny'), `created_at`, `updated_at`)
- `price_items` (PK `id`, FK `price_id`→`prices.id`, FK `vehicle_type_id`, `wash_type` ENUM('zewnetrzne','wewnetrzne','kompleksowe'), `net_amount` DECIMAL(10,2), `tax_rate` DECIMAL(5,2) default 23.00, `currency` CHAR(3) default 'PLN', `created_at`, `updated_at`)
- Indeksy: IDX `price_id`, IDX `vehicle_type_id`, IDX (`wash_type`,`vehicle_type_id`).

### 2.5 Mycia / usługi
- `wash_bays` (PK `id`, `name`, `location`, `active` BOOL)
- `wash_sessions` (PK `id`, FK `vehicle_id`→`vehicles.id`, FK `company_id`→`companies.id`, FK `wash_bay_id`→`wash_bays.id`, FK `operator_id`→`users.id`, `entry_at` DATETIME NN, `exit_at` DATETIME nullable, `wash_type` ENUM('zewnetrzne','wewnetrzne','kompleksowe'), `duration_minutes` SMALLINT, `status` ENUM('wykonane','anulowane','korekta'), `raw_cost` DECIMAL(10,2), `final_cost` DECIMAL(10,2), `currency` CHAR(3) default 'PLN', `price_item_id` FK nullable, `notes`, `created_at`, `updated_at`)
- Indeksy: IDX `entry_at`, IDX `company_id`, IDX `vehicle_id`, IDX `operator_id`, IDX `wash_type`, IDX `status`.

### 2.6 Rozliczenia i fakturowanie
- `billing_batches` (PK `id`, FK `company_id`, `period_start`, `period_end`, `status` ENUM('otwarty','zamkniety','wyeksportowany'), `total_net`, `total_vat`, `total_gross`, `created_at`, `updated_at`)
- `billing_items` (PK `id`, FK `billing_batch_id`→`billing_batches.id`, FK `wash_session_id`→`wash_sessions.id`, `net_amount`, `vat_amount`, `gross_amount`, `currency`, `created_at`)
- Indeksy: IDX `billing_batch_id`, UQ (`billing_batch_id`,`wash_session_id`).

### 2.7 Logi i audyt
- `audit_logs` (PK `id`, `entity` VARCHAR(64), `entity_id`, `action` ENUM('insert','update','delete'), `changed_by` FK→`users.id`, `changed_at`, `before_data` JSON, `after_data` JSON, IDX `entity`, IDX `changed_at`).
- `login_logs` (PK `id`, FK `user_id`, `login_at`, `ip`, `user_agent`, `status` ENUM('ok','fail'), IDX `login_at`).

### 2.8 Dodatkowe indeksy i spójność
- Wszystkie FK z ON UPDATE CASCADE, ON DELETE RESTRICT (poza słownikami, gdzie można ON DELETE SET NULL jeśli biznesowo uzasadnione).
- Tabele operacyjne z kolumnami `created_at`/`updated_at` (DEFAULT CURRENT_TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP).

## 3. Kluczowe funkcjonalności i logika
### 3.1 Operacyjne
- **Szybkie dodawanie mycia**: formularz w panelu operatora z autouzupełnianiem po rejestracji (`vehicles.registration_number`, indeks UQ). W przypadku braku pojazdu – opcja szybkiego dodania z minimalnym zestawem pól (rejestracja, typ, firma).
- **Walidacja i blokady duplikatów**: unikalność rejestracji, walidacja daty/zakresu, blokada wielokrotnego otwarcia sesji dla tego samego pojazdu bez zamknięcia poprzedniej (`wash_sessions` status != 'wykonane'/'anulowane').
- **Automatyczne podpowiadanie firmy**: na podstawie pojazdu (FK `company_id`) lub ostatniego mycia danego numeru rejestracyjnego.
- **Rejestr czasu**: automatyczny zapis `entry_at`, zamknięcie ustala `exit_at` i `duration_minutes`.
- **Korekty/anulowania**: status `korekta`/`anulowane`, powiązanie z logiem audytu i opcją korekty kosztu.

### 3.2 Finansowe
- **Cennik globalny i firmowy**: wybór cennika odbywa się przez wyszukanie aktywnego `price_item` (priorytet: firmowy → globalny) na datę `entry_at`, typ pojazdu i `wash_type`. 
- **Rozliczenia cykliczne**: generator `billing_batches` (np. miesiąc) selekcjonuje zakończone `wash_sessions` bez przypisanego batcha; tworzy `billing_items` i wylicza sumy.
- **Eksport do fakturowania**: batch w statusie „zamkniety” eksportowany do PDF/CSV/XLSX dla księgowości. Zmiana statusu to operacja audytowana.

### 3.3 Raporty
- **Silnik raportowy**: warstwa zapytań SQL z filtrami na dowolnym polu (firma, pojazd, typ, operator, stanowisko, status, zakres dat, cennik). Zapytania korzystają z indeksów dat/kluczy.
- **Raport bazowy (raw)**: widok materializowany lub zapytanie łączące `wash_sessions` + `vehicles` + `companies` + `users` + `price_items` do dalszych eksportów.
- **Raporty agregacyjne**: dzienne/miesięczne/roczne (GROUP BY data), per firma, per pojazd, per pracownik, finansowe (sumy netto/VAT/brutto), wydajność stanowisk (czas trwania, liczba myć).
- **Eksport**: generatory CSV i XLSX; PDF przez bibliotekę typu mPDF/DOMPDF. Brak limitu zakresu – stronicowanie w UI, streaming eksportu, ewentualnie job asynchroniczny dla dużych wolumenów.

## 4. Moduły systemu (wysoki poziom)
- **Auth & RBAC**: logowanie, role (`admin`, `manager`, `operator`, `raporty`), sesje użytkowników, logi logowań.
- **Firmy/Klienci**: CRUD firm, umowy, cenniki firmowe, status aktywności.
- **Flota**: CRUD pojazdów, import CSV, status aktywny/wycofany, przypisania do firm.
- **Cenniki**: globalne i firmowe; historia zmian, walidacja zakresów dat.
- **Mycia/Operacje**: rejestracja wjazdu/wyjazdu, przypisanie operatora/stanowiska, kalkulacja kosztu z cennika, korekty/anulowania.
- **Pracownicy i zmiany**: ewidencja pracowników, przypisania do zmian, raporty wydajności.
- **Rozliczenia**: generowanie batchy rozliczeniowych, sumowanie netto/VAT/brutto, eksport do fakturowania.
- **Raporty**: filtry i agregacje, eksporty, raport bazowy.
- **Audyt**: śledzenie zmian rekordów, kto/kiedy/co zmienił (dane przed/po).

## 5. Panel administracyjny
- Zarządzanie firmami, flotą, pracownikami, zmianami.
- Konfiguracja cenników globalnych i firmowych, walidacja nakładania zakresów dat.
- Dostęp do pełnych raportów i eksportów finansowych.
- Uprawnienia: widoki i akcje zależne od roli; ACL na kontrolerach i akcjach.

## 6. Panel operatora
- Interfejs dotykowy, minimalna liczba pól: rejestracja (autouzupełnianie), typ mycia, stanowisko, operator (z sesji), wjazd/wyjazd, notatka.
- Możliwość szybkiego dopisania pojazdu, ale bez edycji finansów.
- Blokady duplikatów i walidacja: sprawdzenie otwartej sesji dla pojazdu.

## 7. Silnik raportowy
- **Widok bazowy**: `vw_wash_details` (JOIN `wash_sessions`, `vehicles`, `companies`, `users`, `price_items`) – kolumny: daty, firma, rejestracja, typ pojazdu, operator, stanowisko, status, koszt netto/brutto, czas trwania, cennik źródłowy.
- **Filtry**: zakres dat, firma, pojazd, operator, stanowisko, typ mycia, status, cennik, typ pojazdu, numer taborowy, batch rozliczeniowy.
- **Agregacje**: COUNT myć, SUM netto/VAT/brutto, AVG czasu trwania, liczba myć per operator/stanowisko/dzień/miesiąc/rok, ranking klientów.
- **Optymalizacja**: indeksy na datach/kluczach, ewentualne partycjonowanie `wash_sessions` po roku/miesiącu przy dużych wolumenach; cache raportów przez tabele pomocnicze lub Redis (opcjonalnie).
- **Eksport**: generator strumieniowy (chunking), kolejka asynchroniczna dla dużych zestawów (CRON/worker), monitorowanie postępu.

## 8. Struktura plików (prosty MVC)
```
/ (root)
├─ public/
│  ├─ index.php           # front controller, routing
│  ├─ assets/             # CSS/JS
├─ app/
│  ├─ config/
│  │  ├─ database.php     # DSN, PDO setup
│  │  └─ routes.php
│  ├─ controllers/        # kontrolery per moduł (CompaniesController, VehiclesController, WashSessionsController,...)
│  ├─ models/             # mapowanie tabel (Company.php, Vehicle.php, WashSession.php ...)
│  ├─ services/           # logika biznesowa (PricingService, BillingService, ReportingService, AuditService)
│  ├─ repositories/       # zapytania SQL, widoki raportowe
│  ├─ views/              # szablony PHP/HTML (admin, operator)
│  ├─ middlewares/        # auth, rbac, csrf
│  ├─ helpers/            # walidatory, formatery, eksporty CSV/XLSX/PDF
│  └─ cron/               # zadania cykliczne (zamknięcie zmian, generowanie batchy, backupy)
├─ storage/
│  ├─ logs/               # logi aplikacyjne
│  └─ exports/            # pliki raportów
└─ docs/
   └─ system_design.md    # niniejszy dokument
```

## 9. Potencjalne problemy i rozwiązania
- **Wydajność raportów na dużych danych**: indeksy na datach i FK, możliwość partycjonowania `wash_sessions`, widoki materializowane dla agregacji miesięcznych, asynchroniczne eksporty.
- **Spójność cenników w czasie**: walidacja zakresów dat (`valid_from`/`valid_to`) z zakazem nakładania; wersjonowanie cenników (status archiwalny) oraz zapis w logu audytu.
- **Błędy operatora**: walidacja formularzy, autocomplete, blokady na otwarte sesje, możliwość korekt z audytem, ograniczone role.
- **Skalowalność na wiele myjni**: dodanie tabeli `sites` (lokalizacje myjni) i FK w `wash_bays`, `wash_sessions`, `users` do filtrowania danych; izolacja per site w raportach.
- **Bezpieczeństwo**: hasła w `users.password_hash` (bcrypt/argon2), CSRF, rate-limit logowania, logi audytu i logowania, minimalne uprawnienia bazy, kopie zapasowe.

## 10. Dalsze etapy rozwoju
- **API**: REST/JSON dla integracji (np. import floty, zewnętrzne systemy TMS), z kluczami API per firma.
- **RFID/OCR**: integracja z czytnikami kart/przepustek i kamerami OCR tablic rejestracyjnych; automatyczne otwarcie sesji na podstawie odczytu.
- **Planowanie zasobów**: moduł kolejkowania wjazdów, SLA na mycia flotowe, monitoring obłożenia stanowisk.
- **Dashboardy**: widżety KPI (liczba myć, średni czas, przychód) w czasie rzeczywistym.
- **Powiadomienia**: e-mail/SMS dla managerów (raport dzienny), alerty o anulowaniach/korektach.
- **Moduł jakości**: checklisty inspekcji wnętrza pojazdu, zdjęcia przed/po (storage + metadane), metryki jakości.

