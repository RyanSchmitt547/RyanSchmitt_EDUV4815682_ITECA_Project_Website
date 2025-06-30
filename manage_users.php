<?php
session_start();
require 'config.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Connect to database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Prevent admin from deleting self
    if ($delete_id !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_users.php");
        exit();
    } else {
        $error = "You cannot delete your own admin account.";
    }
}

// Handle user creation
if (isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username && $email && $password && $role) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_users.php");
        exit();
    } else {
        $error = "Please fill in all fields to create a user.";
    }
}

// Handle user update
if (isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if ($username && $email && $role) {
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $role, $user_id);
        }
        $stmt->execute();
        $stmt->close();

        header("Location: manage_users.php");
        exit();
    } else {
        $error = "Please fill in all required fields to update the user.";
    }
}

// Fetch all users
$result = $conn->query("SELECT id, username, email, role FROM users ORDER BY id ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        form.inline {
            display: inline;
        }
        .error {
            color: red;
        }
        .form-container {
            margin-top: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        input[type=text], input[type=email], input[type=password], select {
            width: 100%;
            padding: 6px;
            margin: 6px 0 12px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type=submit] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
        }
        input[type=submit]:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background-color: #a71d2a;
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Panel - Manage Users</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a> |
            <a href="manage_products.php">Manage Products</a> |
            <a href="reports.php">Reports</a> |
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <h2>Users List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <button onclick="showEditForm(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['username'])) ?>', '<?= htmlspecialchars(addslashes($user['email'])) ?>', '<?= $user['role'] ?>')">Edit</button>

                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <form class="inline" method="get" onsubmit="return confirm('Are you sure you want to delete this user?');" action="manage_users.php">
                                    <input type="hidden" name="delete" value="<?= $user['id'] ?>" />
                                    <input type="submit" class="btn-delete" value="Delete" />
                                </form>
                            <?php else: ?>
                                <em>(You)</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="form-container">
            <h2>Add New User</h2>
            <form method="POST" action="manage_users.php" id="createUserForm">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required />

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required />

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />

                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select role</option>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>

                <input type="submit" name="create_user" value="Create User" />
            </form>
        </div>

        <!-- Hidden Edit User Form -->
        <div class="form-container" id="editUserContainer" style="display:none;">
            <h2>Edit User</h2>
            <form method="POST" action="manage_users.php" id="editUserForm">
                <input type="hidden" name="user_id" id="editUserId" />

                <label for="editUsername">Username</label>
                <input type="text" id="editUsername" name="username" required />

                <label for="editEmail">Email</label>
                <input type="email" id="editEmail" name="email" required />

                <label for="editPassword">Password (leave blank to keep current)</label>
                <input type="password" id="editPassword" name="password" />

                <label for="editRole">Role</label>
                <select id="editRole" name="role" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>

                <input type="submit" name="update_user" value="Update User" />
                <button type="button" onclick="hideEditForm()">Cancel</button>
            </form>
        </div>

    </main>

    <script>
        function showEditForm(id, username, email, role) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value = role;

            document.getElementById('editUserContainer').style.display = 'block';
            window.scrollTo(0, document.body.scrollHeight);
        }

        function hideEditForm() {
            document.getElementById('editUserContainer').style.display = 'none';
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
