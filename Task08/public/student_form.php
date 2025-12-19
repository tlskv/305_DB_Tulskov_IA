<?php
require_once 'config.php';

$pdo = getPDO();
$student = null;
$isEdit = false;
$error = '';

if (isset($_GET['id'])) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch();
}

$currentYear = (int)date('Y');
$stmt = $pdo->prepare("SELECT id, number FROM groups WHERE graduation_year >= ? ORDER BY number");
$stmt->execute([$currentYear]);
$groups = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    $student_id = trim($_POST['student_id']);
    $group_id = (int)$_POST['group_id'];
    
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
        $checkStmt->execute([$student_id]);
        $existing = $checkStmt->fetch();
        
        if ($existing && (!$isEdit || $existing['id'] != $_POST['id'])) {
            $error = "Студент с таким номером зачетной книжки уже существует!";
        } else {
            if ($isEdit) {
                $stmt = $pdo->prepare("UPDATE students SET 
                    name_student = ?, gender = ?, birth_date = ?, 
                    student_id = ?, group_id = ? 
                    WHERE id = ?");
                $stmt->execute([$name, $gender, $birth_date, $student_id, $group_id, $_POST['id']]);
                $message = "Данные обновлены";
            } else {
                $stmt = $pdo->prepare("INSERT INTO students 
                    (name_student, gender, birth_date, student_id, group_id) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $gender, $birth_date, $student_id, $group_id]);
                $message = "Студент добавлен";
            }
            
            $_SESSION['message'] = $message;
            redirect('index.php');
        }
    } catch (PDOException $e) {
        $error = "Ошибка: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Редактировать студента' : 'Добавить студента' ?></title>
</head>
<body>
    <h1><?= $isEdit ? 'Редактировать студента' : 'Добавить студента' ?></h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']) ?>">
        <?php endif; ?>
        
        <p>
            <label>ФИО:</label><br>
            <input type="text" name="name" value="<?= $isEdit ? htmlspecialchars($student['name_student']) : '' ?>" required>
        </p>
        
        <p>
            <label>Пол:</label><br>
            <input type="radio" name="gender" value="М" <?= ($isEdit && $student['gender'] == 'М') ? 'checked' : '' ?> required> Мужской
            <input type="radio" name="gender" value="Ж" <?= ($isEdit && $student['gender'] == 'Ж') ? 'checked' : '' ?>> Женский
        </p>
        
        <p>
            <label>Дата рождения:</label><br>
            <input type="date" name="birth_date" value="<?= $isEdit ? htmlspecialchars($student['birth_date']) : '' ?>" required>
        </p>
        
        <p>
            <label>Зачетная книжка:</label><br>
            <input type="text" name="student_id" value="<?= $isEdit ? htmlspecialchars($student['student_id']) : '' ?>" required>
        </p>
        
        <p>
            <label>Группа:</label><br>
            <select name="group_id" required>
                <option value="">Выберите группу</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>" 
                        <?= ($isEdit && $student['group_id'] == $group['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group['number']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <button type="submit"><?= $isEdit ? 'Сохранить' : 'Добавить' ?></button>
            <a href="index.php">Отмена</a>
        </p>
    </form>
</body>
</html>
<?php $pdo = null; ?>