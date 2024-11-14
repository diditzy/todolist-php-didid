<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];

        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $due_date]);
    }

    if ($_POST['action'] == 'edit') {
        $task_id = $_POST['task_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];

        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $due_date, $task_id, $user_id]);
    }
}

if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your To-Do List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/todo.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
        <a class="navbar-brand text-light" href="#">TO-DO LIST</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Welcome, <?= htmlspecialchars($user['username']) ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="?logout=true">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4 text-center">Your To-Do List</h2>

        <form method="POST" class="mb-4">
            <div class="form-group">
                <input type="text" name="title" class="form-control" placeholder="Task Title" required>
            </div>
            <div class="form-group">
                <textarea name="description" class="form-control" placeholder="Description"></textarea>
            </div>
            <div class="form-group">
                <input type="date" name="due_date" class="form-control">
            </div>
            <input type="hidden" name="action" value="add">
            <button type="submit" class="btn btn-primary btn-block">Add Task</button>
        </form>

        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Task Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <?php
                        // Get current date and compare with task's due date
                        $today = date('Y-m-d');
                        $due_date = $task['due_date'];
                        
                        // Determine due date status
                        if ($due_date < $today) {
                            $due_date_class = 'overdue'; // Task is overdue
                        } elseif ($due_date == $today) {
                            $due_date_class = 'today'; // Task is due today
                        } else {
                            $due_date_class = 'upcoming'; // Task is upcoming
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['description']) ?></td>
                        <td class="<?= $due_date_class ?>"><?= htmlspecialchars($task['due_date']) ?></td>
                        <td>
                            <a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal" data-id="<?= $task['id'] ?>" data-title="<?= htmlspecialchars($task['title']) ?>" data-description="<?= htmlspecialchars($task['description']) ?>" data-due_date="<?= $task['due_date'] ?>">Edit</a>
                            <a href="?delete=<?= $task['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Editing Tasks -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Task</h5>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" name="title" id="editTitle" class="form-control" placeholder="Task Title" required>
                        </div>
                        <div class="form-group">
                            <textarea name="description" id="editDescription" class="form-control" placeholder="Description"></textarea>
                        </div>
                        <div class="form-group">
                            <input type="date" name="due_date" id="editDueDate" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="task_id" id="editTaskId">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for Modal Interaction -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var taskId = button.data('id');
            var taskTitle = button.data('title');
            var taskDescription = button.data('description');
            var taskDueDate = button.data('due_date');

            var modal = $(this);
            modal.find('#editTitle').val(taskTitle);
            modal.find('#editDescription').val(taskDescription);
            modal.find('#editDueDate').val(taskDueDate);
            modal.find('#editTaskId').val(taskId);
        });
    </script>
</body>
</html>
