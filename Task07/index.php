<?php
if (!file_exists('students.db')) {
    die("Файл базы данных не найден.");
}

try {
    $pdo = new PDO('sqlite:students.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$currentYear = (int)date('Y');

try {
    $sqlGroups = "SELECT DISTINCT number FROM groups WHERE graduation_year >= :currentYear ORDER BY number";
    $stmtGroups = $pdo->prepare($sqlGroups);
    $stmtGroups->execute(['currentYear' => $currentYear]);
    $groups = $stmtGroups->fetchAll(PDO::FETCH_COLUMN, 0);
    $groups = array_map('strval', $groups);
} catch (PDOException $e) {
    die("Ошибка при получении списка групп: " . $e->getMessage());
}

$selectedGroup = $_GET['group'] ?? '';

$showAllGroups = empty($selectedGroup);

try {
    if ($showAllGroups) {
        $sql = "SELECT 
                    g.number as group_number,
                    g.direction,
                    s.name_student,
                    s.gender,
                    s.birth_date,
                    s.student_id
                FROM students s
                JOIN groups g ON s.group_id = g.id
                WHERE g.graduation_year >= :currentYear
                ORDER BY g.number, s.name_student";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['currentYear' => $currentYear]);
    } else {
        $sql = "SELECT 
                    g.number as group_number,
                    g.direction,
                    s.name_student,
                    s.gender,
                    s.birth_date,
                    s.student_id
                FROM students s
                JOIN groups g ON s.group_id = g.id
                WHERE g.graduation_year >= :currentYear AND g.number = :groupNumber
                ORDER BY s.name_student";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'currentYear' => $currentYear,
            'groupNumber' => (int)$selectedGroup
        ]);
    }
    
    $students = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Ошибка при получении данных: " . $e->getMessage());
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список студентов</title>
    <style>
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Список студентов</h1>
    
    <form method="GET" action="">
        <label for="group">Группа:</label>
        <select name="group" id="group" onchange="this.form.submit()">
            <option value="">Все группы</option>
            <?php foreach ($groups as $group): ?>
                <option value="<?= htmlspecialchars($group) ?>" 
                    <?= $selectedGroup == $group ? 'selected' : '' ?>>
                    <?= htmlspecialchars($group) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    
    <?php if (!empty($students)): ?>
        <?php if ($showAllGroups): ?>
            <h2>Все студенты (<?= count($students) ?> человек)</h2>
        <?php else: ?>
            <h2>Группа <?= htmlspecialchars($selectedGroup) ?> (<?= count($students) ?> человек)</h2>
        <?php endif; ?>
        
        <table>
            <tr>
                <th>Группа</th>
                <th>Направление подготовки</th>
                <th>ФИО</th>
                <th>Пол</th>
                <th>Дата рождения</th>
                <th>Зачетная книжка</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <?php
                $birthDate = !empty($student['birth_date']) ? 
                    date('d.m.Y', strtotime($student['birth_date'])) : 
                    'н/д';
                ?>
                <tr>
                    <td><?= htmlspecialchars($student['group_number']) ?></td>
                    <td><?= htmlspecialchars($student['direction']) ?></td>
                    <td><?= htmlspecialchars($student['name_student']) ?></td>
                    <td><?= htmlspecialchars($student['gender']) ?></td>
                    <td><?= htmlspecialchars($birthDate) ?></td>
                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Нет данных о студентах.</p>
    <?php endif; ?>
</body>
</html>