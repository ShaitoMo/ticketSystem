<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'controllers/AdminController.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Instantiate AdminController
$AdminController = new AdminController();
$campuses = $AdminController->getAllCampuses();
$departments = $AdminController->getAllDepartments(); 
$roles = $AdminController->getRolesOfUsers();

// Handle form submission for adding a single user
if (isset($_POST['action']) && $_POST['action'] == 'add_user') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $department_id = $_POST['department_id'];
    $phone_number = $_POST['phone_number'];
    $campus_id = $_POST['campus_id'];

    // Add the user using AdminController
    $result = $AdminController->AddUser($first_name, $last_name, $password, $email, $role, $department_id, $phone_number, $campus_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User successfully added!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user. Please try again.']);
    }
    exit();
}

// Handle form submission for bulk user upload
if (isset($_POST['action']) && $_POST['action'] == 'upload_file' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    // Determine file type
    $fileType = IOFactory::identify($file);
    $reader = IOFactory::createReader($fileType);

    $spreadsheet = $reader->load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $data = $worksheet->toArray();

    // Extract header
    $header = array_shift($data);

    // Prepare array to hold user data
    $users = [];
    
    foreach ($data as $row) {
        $users[] = [
            'first_name' => $row[0],
            'last_name' => $row[1],
            'email' => $row[2],
            'password' => $row[3], // Ideally hashed before saving
            'role' => $row[4],
            'department_id' => $row[5],
            'phone_number' => $row[6],
            'campus_id' => $row[7]
        ];
    }

    // Add users using AdminController
    $result = $AdminController->addBulkUsers($users);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Users successfully added!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add users. Please try again.']);
    }
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f9; /* Light gray background */
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            width: 100%; /* Full width */
            max-width: 1000px; /* Maximum width */
            margin: auto;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .form-label {
            font-weight: bold;
        }
        .form-select, .form-control {
            height: 38px;
        }
    </style>
</head>
<body>
<?php include_once('header.php'); ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="form-container">
                    <h1 class="text-center text-primary mb-4">Add New User</h1>

                    <!-- Display success or error message -->
                    <div id="message"></div>

                    <form id="addUserForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name:</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name:</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="campus_id" class="form-label">Campus:</label>
                                <select id="campus_id" name="campus_id" class="form-select" required onchange="filterDepartments()">
                                    <option value="">Select Campus</option>
                                    <?php foreach ($campuses as $campus): ?>
                                        <option value="<?php echo $campus['id']; ?>"><?php echo $campus['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department:</label>
                                <select id="department_id" name="department_id" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <!-- JavaScript will populate this based on the campus selected -->
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role:</label>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label">Phone Number:</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Add User</button>
                    </form>

                    <!-- Bulk User Upload Form -->
                    <form id="bulkUploadForm" method="POST" enctype="multipart/form-data">
                        <div class="my-4">
                            <label for="file" class="form-label">Upload CSV/Excel File:</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".csv, .xlsx, .xls" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Upload and Add Users</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const departments = <?php echo json_encode($departments); ?>;

        function filterDepartments() {
            const campusId = document.getElementById('campus_id').value;
            const departmentSelect = document.getElementById('department_id');

            // Clear previous options
            departmentSelect.innerHTML = '<option value="">Select Department</option>';

            // Filter and append departments based on selected campus
            departments.forEach(department => {
                if (department.campus_id == campusId) {
                    const option = document.createElement('option');
                    option.value = department.id;
                    option.text = department.name;
                    departmentSelect.appendChild(option);
                }
            });
        }

     // For adding a user
document.getElementById('addUserForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting normally

    const formData = new FormData(this);
    formData.append('action', 'add_user');

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Store the message and success status in localStorage
        localStorage.setItem('message', data.message);
        localStorage.setItem('alertType', data.success ? 'success' : 'danger');

        // Reload the page
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// For bulk upload
document.getElementById('bulkUploadForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting normally

    const formData = new FormData(this);
    formData.append('action', 'upload_file');

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Store the message and success status in localStorage
        localStorage.setItem('message', data.message);
        localStorage.setItem('alertType', data.success ? 'success' : 'danger');

        // Reload the page
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// Display the alert after page reload
window.addEventListener('load', function() {
    const message = localStorage.getItem('message');
    const alertType = localStorage.getItem('alertType');

    if (message) {
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = `<div class="alert alert-${alertType}">${message}</div>`;
        
        // Clear the stored message after showing it
        localStorage.removeItem('message');
        localStorage.removeItem('alertType');
    }
});

    </script>
</body>
</html>
