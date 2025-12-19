<?php
require_once 'config.php';

$pdo = getPDO();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM exam_results WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $exam = $stmt->fetch();
}

$student_id = $_GET['student_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM exam_results WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $_SESSION['message'] = "Экзамен удален";
        redirect("exam.php?student_id=" . $_POST['student_id']);
    } catch (PDOException $e) {
        die("Ошибка при удалении: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление экзамена</title>
</head>
<body>
    <h2>Удаление экзамена</h2>
    
    <?php if (isset($exam)): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
            <input type="hidden" name="student_id" value="<?= $_GET['student_id'] ?>">
            <p><strong>Вы уверены, что хотите удалить запись об экзамене?</strong></p>
            <button type="submit">Да</button>
            <a href="exam.php?student_id=<?= $_GET['student_id'] ?>">Отмена</a>
        </form>
    <?php else: ?>
        <p>Запись не найдена</p>
        <a href="exam.php?student_id=<?= $student_id ?>">Назад</a>
    <?php endif; ?>
</body>
</html>