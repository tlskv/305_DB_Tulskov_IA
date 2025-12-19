<?php

require_once 'config.php';

$pdo = getPDO();
$exam = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM exam_results WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $exam = $stmt->fetch();
}

$student_id = $_GET['student_id'] ?? $_POST['student_id'] ?? 0;

$student = null;
$current_group_id = null;
if ($student_id) {
    $stmt = $pdo->prepare("SELECT s.*, g.id as group_id, g.direction 
                          FROM students s 
                          JOIN groups g ON s.group_id = g.id 
                          WHERE s.id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    $current_group_id = $student['group_id'] ?? null;
}

$groups = $pdo->query("SELECT * FROM groups ORDER BY number")->fetchAll();

$students = [];
if ($current_group_id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE group_id = ? ORDER BY name_student");
    $stmt->execute([$current_group_id]);
    $students = $stmt->fetchAll();
}

$disciplines = [];
if ($student && isset($student['direction'])) {
    $stmt = $pdo->prepare("SELECT * FROM disciplines WHERE direction = ? ORDER BY course, name");
    $stmt->execute([$student['direction']]);
    $disciplines = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    try {
        $data = [
            $_POST['student_id'],
            $_POST['discipline_id'],
            $_POST['exam_date'],
            (int)$_POST['grade']
        ];
        
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE exam_results SET 
                student_id=?, discipline_id=?, exam_date=?, grade=? 
                WHERE id=?");
            $stmt->execute([...$data, $_POST['id']]);
            $message = "Экзамен обновлен";
        } else {
            $stmt = $pdo->prepare("INSERT INTO exam_results 
                (student_id, discipline_id, exam_date, grade) 
                VALUES (?, ?, ?, ?)");
            $stmt->execute($data);
            $message = "Экзамен добавлен";
        }
        
        $_SESSION['message'] = $message;
        redirect("exam.php?student_id=" . $_POST['student_id']);
    } catch (PDOException $e) {
        die("Ошибка при сохранении: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Редактирование экзамена' : 'Новый экзамен' ?></title>
</head>
<body>
    <h2><?= $isEdit ? 'Редактирование экзамена' : 'Новый экзамен' ?></h2>
    
    <form method="POST" id="mainForm">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $exam['id'] ?>">
        <?php endif; ?>
        
        <input type="hidden" name="student_id" id="student_id_field" value="<?= $student_id ?>">
        
        <p>
            <label>Студент:</label><br>
            <select name="student_select" id="student_select" required>
                <option value="">Выберите студента</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>" 
                        <?= $student_id == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name_student']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label>Дисциплина:</label><br>
            <select name="discipline_id" required>
                <option value="">Выберите дисциплину</option>
                <?php foreach ($disciplines as $d): ?>
                    <option value="<?= $d['id'] ?>" 
                        <?= ($isEdit && $exam['discipline_id'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?> 
                        (Курс <?= $d['course'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label>Дата экзамена:</label><br>
            <input type="date" name="exam_date" 
                   value="<?= $isEdit ? $exam['exam_date'] : date('Y-m-d') ?>" required>
        </p>

        <p>
            <label>Оценка:</label><br>
            <select name="grade" required>
                <option value="5" <?= ($isEdit && $exam['grade'] == 5) ? 'selected' : '' ?>>5 (Отлично)</option>
                <option value="4" <?= ($isEdit && $exam['grade'] == 4) ? 'selected' : '' ?>>4 (Хорошо)</option>
                <option value="3" <?= ($isEdit && $exam['grade'] == 3) ? 'selected' : '' ?>>3 (Удовлетворительно)</option>
                <option value="2" <?= ($isEdit && $exam['grade'] == 2) ? 'selected' : '' ?>>2 (Неудовлетворительно)</option>
            </select>
        </p>

        <button type="submit" name="save">Сохранить</button>
        <?php if($student_id): ?>
            <a href="exam.php?student_id=<?= $student_id ?>">Отмена</a>
        <?php else: ?>
            <a href="index.php">Отмена</a>
        <?php endif; ?>
    </form>
    
    <script>
        document.getElementById('student_select').addEventListener('change', function() {
            document.getElementById('student_id_field').value = this.value;
        });
    </script>
</body>
</html>
<?php $pdo = null; ?>