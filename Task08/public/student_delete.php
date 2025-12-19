<?php
require_once 'config.php';

$pdo = getPDO();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT s.*, g.number as group_number FROM students s 
                           JOIN groups g ON s.group_id = g.id WHERE s.id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['confirm'] === 'yes') {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $_SESSION['message'] = "Студент удален";
    }
    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление студента</title>
</head>
<body>
    <h1>Удаление студента</h1>
    
    <?php if (isset($student)): ?>
        <p><strong>Вы уверены, что хотите удалить студента?</strong></p>
        
        <p>ФИО: <?= htmlspecialchars($student['name_student']) ?></p>
        <p>Группа: <?= htmlspecialchars($student['group_number']) ?></p>
        <p>Зачетка: <?= htmlspecialchars($student['student_id']) ?></p>
        
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']) ?>">
            
            <p>
                <input type="radio" name="confirm" value="yes" required> Да
                <input type="radio" name="confirm" value="no"> Нет
            </p>
            
            <p>
                <button type="submit">Подтвердить</button>
                <a href="index.php">Отмена</a>
            </p>
        </form>
    <?php else: ?>
        <p>Студент не найден</p>
        <p><a href="index.php">Назад</a></p>
    <?php endif; ?>
</body>
</html>
<?php $pdo = null; ?>