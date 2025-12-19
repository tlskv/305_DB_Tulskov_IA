<?php

if (!file_exists('students.db')) {
    die("Ошибка: Файл базы данных не найден.\n");
}

try {
    $pdo = new PDO('sqlite:students.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage() . "\n");
}

$currentYear = (int)date('Y');

try {
    $sqlGroups = "SELECT DISTINCT number FROM groups WHERE graduation_year >= :currentYear ORDER BY number";
    $stmtGroups = $pdo->prepare($sqlGroups);
    $stmtGroups->execute(['currentYear' => $currentYear]);
    $groups = $stmtGroups->fetchAll(PDO::FETCH_COLUMN, 0);

    $groups = array_map('strval', $groups);
    
    if (empty($groups)) {
        echo "Нет действующих групп в базе данных.\n";
        exit(0);
    }
} catch (PDOException $e) {
    die("Ошибка при получении списка групп: " . $e->getMessage() . "\n");
}

echo "Список групп\n";

foreach ($groups as $group) {
    echo "$group\n";
}

echo "Введите номер группы(Enter для всех групп): ";
$input = trim(fgets(STDIN));

$selectedGroup = null;
if (!empty($input)) {
    $found = false;
    foreach ($groups as $group) {
        if ((string)$group === (string)$input) {
            $found = true;
            $selectedGroup = $input;
            break;
        }
    }
    
    if (!$found) {
        echo "\nОшибка: Группа '$input' не найдена\n";
        echo "Список групп\n";
        foreach ($groups as $group) {
            echo "$group\n";
        }
        exit(1);
    }
    echo "\nСписок группы: $selectedGroup\n";
} else {
    echo "\nВсе группы\n";
}

try {
    $sql = "SELECT 
                g.number as group_number,
                g.direction,
                s.name_student,
                s.gender,
                s.birth_date,
                s.student_id
            FROM students s
            JOIN groups g ON s.group_id = g.id
            WHERE g.graduation_year >= :currentYear";
    
    $params = ['currentYear' => $currentYear];
    
    if ($selectedGroup) {
        $sql .= " AND g.number = :groupNumber";
        $params['groupNumber'] = (int)$selectedGroup;
    }
    
    $sql .= " ORDER BY g.number, s.name_student";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
    if (empty($students)) {
        echo "\nНет данных о студентах в группе.\n";
        exit(0);
    }
    
} catch (PDOException $e) {
    die("Ошибка при получении данных: " . $e->getMessage() . "\n");
}

function drawTable($data) {
    if (empty($data)) return;
    
    echo "┌────────┬────────────────────────┬───────────────────────────────────┬─────┬───────────────┬─────────────────┐\n";
    echo "│ Группа │ Направление подготовки │ ФИО                               │ Пол │ Дата рождения │ Зачетная книжка │\n";
    echo "├────────┼────────────────────────┼───────────────────────────────────┼─────┼───────────────┼─────────────────┤\n";
    
    foreach ($data as $row) {
        $birth = $row['birth_date'] ? date('d.m.Y', strtotime($row['birth_date'])) : 'н/д';

        $name = trim($row['name_student']);
        $nameLength = mb_strlen($name);

        if ($nameLength < 33) {
            $name = $name . str_repeat(' ', 33 - $nameLength);
        } 
        
        $group = str_pad($row['group_number'], 6, ' ', STR_PAD_RIGHT);
        $direction = str_pad($row['direction'] . ' ', 26, ' ', STR_PAD_RIGHT);
        $gender = str_pad($row['gender'] . ' ', 4, ' ', STR_PAD_RIGHT);
        $birthDate = str_pad($birth . '   ', 12, ' ', STR_PAD_RIGHT);
        $studentId = str_pad($row['student_id'], 15, ' ', STR_PAD_RIGHT);   
        
        echo "│ {$group} │ {$direction} │ {$name} │ {$gender} │ {$birthDate} │ {$studentId} │\n";
    }

    echo "└────────┴────────────────────────┴───────────────────────────────────┴─────┴───────────────┴─────────────────┘\n";
    echo "Количество студентов: " . count($data) . "\n"; 
}

echo "\n";
echo "Список студентов\n";

drawTable($students);
$pdo = null;
?>