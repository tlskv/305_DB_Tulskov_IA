-- Active: 1765044604730@@127.0.0.1@3306
DROP TABLE IF EXISTS work_records;
DROP TABLE IF EXISTS client_list_services;
DROP TABLE IF EXISTS client_list;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS worker;

CREATE TABLE worker (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    position TEXT NOT NULL,
    hire_date TEXT NOT NULL,
    dismissal_date TEXT,
    salary_percentage REAL NOT NULL CHECK(salary_percentage >= 0 AND salary_percentage <= 100),
    status TEXT NOT NULL DEFAULT 'работает' CHECK(status IN ('работает', 'уволен')),
    phone TEXT
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price REAL NOT NULL CHECK(price >= 0)
);

CREATE TABLE schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    worker_id INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL CHECK(day_of_week >= 1 AND day_of_week <= 7),
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    FOREIGN KEY (worker_id) REFERENCES worker(id) ON DELETE CASCADE
);

CREATE TABLE client_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    worker_id INTEGER NOT NULL,
    client_name TEXT NOT NULL,
    client_phone TEXT,
    client_date TEXT NOT NULL,
    client_time TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'запланирована' CHECK(status IN ('запланирована', 'выполнена', 'отменена')),
    total_price REAL NOT NULL DEFAULT 0 CHECK(total_price >= 0),
    FOREIGN KEY (worker_id) REFERENCES worker(id) ON DELETE RESTRICT
);

CREATE TABLE client_list_services (
    client_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    PRIMARY KEY (client_id, service_id),
    FOREIGN KEY (client_id) REFERENCES client_list(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE work_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL,
    worker_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    work_date TEXT NOT NULL,
    work_time TEXT NOT NULL,
    revenue REAL NOT NULL CHECK(revenue >= 0),
    FOREIGN KEY (client_id) REFERENCES client_list(id) ON DELETE RESTRICT,
    FOREIGN KEY (worker_id) REFERENCES worker(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

INSERT INTO worker (id, name, position, hire_date, dismissal_date, salary_percentage, status, phone) VALUES
(1, 'Соколов Алексей Владимирович', 'Мастер', '2023-02-10', NULL, 28.5, 'работает', '+7-901-111-22-33'),
(2, 'Кузнецов Дмитрий Николаевич', 'Старший мастер', '2022-05-15', NULL, 32.0, 'работает', '+7-901-222-33-44'),
(3, 'Морозов Игорь Петрович', 'Мастер', '2021-08-20', '2024-09-10', 27.0, 'уволен', '+7-901-333-44-55'),
(4, 'Волков Сергей Александрович', 'Мастер-диагност', '2023-06-25', NULL, 30.5, 'работает', '+7-901-444-55-66'),
(5, 'Лебедев Павел Олегович', 'Мастер', '2022-11-30', NULL, 26.5, 'работает', '+7-901-555-66-77'),
(6, 'Орлова Анна Викторовна', 'Мастер', '2024-01-15', NULL, 25.0, 'работает', '+7-901-666-77-88');

INSERT INTO services (id, name, duration_minutes, price) VALUES
(1, 'Замена моторного масла', 45, 2800.00),
(2, 'Замена тормозных дисков', 90, 8500.00),
(3, 'Полная компьютерная диагностика', 60, 3500.00),
(4, 'Замена салонного фильтра', 20, 1200.00),
(5, 'Шиномонтаж 4 колес', 60, 3200.00),
(6, 'Регулировка развал-схождения', 75, 5000.00),
(7, 'Замена амортизаторов', 120, 9500.00),
(8, 'Диагностика и замена АКБ', 30, 4000.00),
(9, 'Промывка инжектора', 50, 4200.00),
(10, 'Замена ремня ГРМ', 180, 12500.00),
(11, 'Химчистка салона', 240, 12000.00),
(12, 'Полировка фар', 40, 3000.00);

INSERT INTO schedules (id, worker_id, day_of_week, start_time, end_time) VALUES
(1, 1, 1, '08:00', '17:00'),
(2, 1, 2, '08:00', '17:00'),
(3, 1, 3, '08:00', '17:00'),
(4, 1, 4, '08:00', '17:00'),
(5, 1, 5, '08:00', '17:00'),
(6, 2, 2, '09:00', '18:00'),
(7, 2, 3, '09:00', '18:00'),
(8, 2, 4, '09:00', '18:00'),
(9, 2, 5, '09:00', '18:00'),
(10, 2, 6, '09:00', '15:00'),
(11, 3, 1, '10:00', '19:00'),
(12, 3, 2, '10:00', '19:00'),
(13, 3, 3, '10:00', '19:00'),
(14, 3, 4, '10:00', '19:00'),
(15, 3, 5, '10:00', '19:00'),
(16, 4, 1, '07:00', '16:00'),
(17, 4, 2, '07:00', '16:00'),
(18, 4, 3, '07:00', '16:00'),
(19, 4, 4, '07:00', '16:00'),
(20, 4, 5, '07:00', '16:00'),
(21, 5, 1, '12:00', '21:00'),
(22, 5, 2, '12:00', '21:00'),
(23, 5, 3, '12:00', '21:00'),
(24, 5, 4, '12:00', '21:00'),
(25, 5, 5, '12:00', '21:00'),
(26, 5, 6, '10:00', '16:00'),
(27, 6, 1, '09:00', '18:00'),
(28, 6, 2, '09:00', '18:00'),
(29, 6, 3, '09:00', '18:00'),
(30, 6, 4, '09:00', '18:00'),
(31, 6, 5, '09:00', '18:00');

INSERT INTO client_list (id, worker_id, client_name, client_phone, client_date, client_time, status, total_price) VALUES
(1, 1, 'Николаев Артем Сергеевич', '+7-912-111-11-11', '2024-12-02', '09:00', 'выполнена', 5000.00),
(2, 1, 'Захарова Мария Дмитриевна', '+7-912-222-22-22', '2024-12-02', '13:00', 'выполнена', 8500.00),
(3, 2, 'Федоров Иван Александрович', '+7-912-333-33-33', '2024-12-03', '10:00', 'выполнена', 3500.00),
(4, 2, 'Семенова Екатерина Павловна', '+7-912-444-44-44', '2024-12-03', '14:00', 'запланирована', 9500.00),
(5, 4, 'Тихонов Андрей Владимирович', '+7-912-555-55-55', '2024-12-04', '08:00', 'выполнена', 7000.00),
(6, 5, 'Белов Роман Игоревич', '+7-912-666-66-66', '2024-12-04', '13:00', 'запланирована', 12500.00),
(7, 1, 'Ковалев Денис Сергеевич', '+7-912-777-77-77', '2024-12-05', '10:00', 'запланирована', 15700.00),
(8, 2, 'Григорьева Ольга Викторовна', '+7-912-888-88-88', '2024-12-05', '11:00', 'запланирована', 2800.00),
(9, 6, 'Дмитриев Максим Алексеевич', '+7-912-999-99-99', '2025-01-10', '10:00', 'запланирована', 12000.00),
(10, 4, 'Алексеева Татьяна Николаевна', '+7-912-101-10-10', '2025-01-10', '14:00', 'запланирована', 5000.00),
(11, 3, 'Петров Константин Ильич', '+7-912-202-20-20', '2024-07-15', '11:00', 'выполнена', 4200.00),
(12, 3, 'Сергеева Анастасия Андреевна', '+7-912-303-30-30', '2024-08-20', '14:00', 'выполнена', 3000.00);

INSERT INTO client_list_services (client_id, service_id) VALUES
(1, 6), -- Регулировка развал-схождения
(2, 2), -- Замена тормозных дисков
(3, 3), -- Компьютерная диагностика
(4, 7), -- Замена амортизаторов
(5, 1), -- Замена масла
(5, 3), -- Диагностика
(6, 10), -- Замена ГРМ
(7, 2), -- Тормозные диски
(7, 6), -- Развал-схождение
(7, 12), -- Полировка фар
(8, 1), -- Замена масла
(9, 11), -- Химчистка
(10, 6), -- Развал-схождение
(11, 9), -- Промывка инжектора
(12, 12); -- Полировка фар

INSERT INTO work_records (id, client_id, worker_id, service_id, work_date, work_time, revenue) VALUES
(1, 1, 1, 6, '2024-12-02', '09:00', 5000.00),
(2, 2, 1, 2, '2024-12-02', '13:00', 8500.00),
(3, 3, 2, 3, '2024-12-03', '10:00', 3500.00),
(4, 5, 4, 1, '2024-12-04', '08:00', 2800.00),
(5, 5, 4, 3, '2024-12-04', '08:45', 4200.00),
(6, 11, 3, 9, '2024-07-15', '11:00', 4200.00),
(7, 12, 3, 12, '2024-08-20', '14:00', 3000.00),
(8, 9, 6, 11, '2025-01-10', '10:00', 12000.00),
(9, 10, 4, 6, '2025-01-10', '14:00', 5000.00);

CREATE INDEX idx_worker_status ON worker(status);
CREATE INDEX idx_worker_name ON worker(name);
CREATE INDEX idx_services_name ON services(name);
CREATE INDEX idx_schedules_worker_id ON schedules(worker_id);
CREATE INDEX idx_client_list_worker_id ON client_list(worker_id);
CREATE INDEX idx_client_list_date ON client_list(client_date);
CREATE INDEX idx_client_list_status ON client_list(status);
CREATE INDEX idx_work_records_worker_id ON work_records(worker_id);
CREATE INDEX idx_work_records_date ON work_records(work_date);
CREATE INDEX idx_work_records_client_id ON work_records(client_id);
CREATE INDEX idx_work_records_service_id ON work_records(service_id);   
