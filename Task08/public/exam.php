<?php
require_once 'config.php';

$pdo = getPDO();
$student_id = $_GET['student_id'] ?? die("Не указан ID студента");

$stmt = $pdo->prepare("SELECT s.*, g.number as group_number FROM students s 
                       JOIN groups g ON s.group_id = g.id WHERE s.id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Студент не найден");
}

$stmt = $pdo->prepare("
    SELECT er.*, d.name as discipline_name 
    FROM exam_results er 
    JOIN disciplines d ON er.discipline_id = d.id 
    WHERE er.student_id = ? 
    ORDER BY er.exam_date DESC
");

$stmt->execute([$student_id]);
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Экзамены студента</title>
</head>
<body>
    <h2>Результаты экзаменов: <?= htmlspecialchars($student['name_student']) ?></h2>
    <p><strong>Группа:</strong> <?= htmlspecialchars($student['group_number']) ?></p>
    
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Дата</th>
            <th>Дисциплина</th>
            <th>Оценка</th>
            <th>Действия</th>
        </tr>
        <?php if (!empty($exams)): ?>
            <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($exam['exam_date'])) ?></td>
                    <td><?= htmlspecialchars($exam['discipline_name']) ?></td>
                    <td><?= $exam['grade'] ?></td>
                    <td>
                        <a href="exam_form.php?id=<?= $exam['id'] ?>&student_id=<?= $student_id ?>">Редактировать</a>
                        <a href="exam_delete.php?id=<?= $exam['id'] ?>&student_id=<?= $student_id ?>" 
                           onclick="return confirm('Удалить запись об экзамене?')">
                            Удалить
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align: center;">Нет записей об экзаменах</td>
            </tr>
        <?php endif; ?>
    </table>
    
    <br>
    <a href="exam_form.php?student_id=<?= $student_id ?>">Добавить экзамен</a>
    <br><br>
    <a href="index.php">Вернуться к списку студентов</a>
</body>
</html>
<?php $pdo = null; ?>