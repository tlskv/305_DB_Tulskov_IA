<?php
require_once 'config.php';

$pdo = getPDO();
$currentYear = (int)date('Y');

$selectedGroup = $_GET['group'] ?? '';

$sqlGroups = "SELECT DISTINCT number FROM groups WHERE graduation_year >= :currentYear ORDER BY number";
$stmtGroups = $pdo->prepare($sqlGroups);
$stmtGroups->execute(['currentYear' => $currentYear]);
$groups = $stmtGroups->fetchAll(PDO::FETCH_COLUMN, 0);
$groups = array_map('strval', $groups);

$sql = "SELECT s.*, g.number as group_number, g.direction 
        FROM students s 
        JOIN groups g ON s.group_id = g.id 
        WHERE g.graduation_year >= :currentYear";
    
$params = ['currentYear' => $currentYear];

if ($selectedGroup && in_array($selectedGroup, $groups)) {
    $sql .= " AND g.number = :groupNumber";
    $params['groupNumber'] = (int)$selectedGroup;
}

$sql .= " ORDER BY g.number, s.name_student";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$totalStudents = count($students);
$showAllGroups = empty($selectedGroup);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список студентов</title>
    <style>
        table { 
            border-collapse: collapse; 
            margin: 20px 0; 
            width: 100%;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .button {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            background-color: #808080;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #696969;
        }
        .actions {
            white-space: nowrap;
        }
        .add-button {
            display: inline-block;
            padding: 8px 16px;
            margin: 10px 0;
            background-color: #808080;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        .add-button:hover {
            background-color: #696969;
        }
        .reset-link {
            margin-left: 10px;
            color: #0066cc;
            text-decoration: none;
        }
        .reset-link:hover {
            text-decoration: underline;
        }
        .filter-form {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Список студентов</h1>
    
    <form method="GET" action="" class="filter-form">
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
        <a href="?" class="reset-link">Сбросить</a>
    </form>
    
    <?php if ($showAllGroups): ?>
        <h2>Все студенты (<?= $totalStudents ?> человек)</h2>
    <?php else: ?>
        <h2>Группа <?= htmlspecialchars($selectedGroup) ?> (<?= $totalStudents ?> человек)</h2>
    <?php endif; ?>
    
    <a href="student_form.php" class="add-button">Добавить студента</a>
    
    <?php if (!empty($students)): ?>
        <table>
            <tr>
                <th>Группа</th>
                <th>Направление подготовки</th>
                <th>ФИО</th>
                <th>Пол</th>
                <th>Дата рождения</th>
                <th>Зачетная книжка</th>
                <th>Действия</th>
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
                    <td class="actions">
                        <a href="student_form.php?id=<?= $student['id'] ?>" class="button">Редактировать</a>
                        <a href="student_delete.php?id=<?= $student['id'] ?>" class="button">Удалить</a>
                        <a href="exam.php?student_id=<?= $student['id'] ?>" class="button">Экзамены</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Нет данных для отображения</p>
    <?php endif; ?>
</body>
</html>
<?php $pdo = null; ?>