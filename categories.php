<?php
require_once 'controllers/AdminController.php';
require_once 'controllers/ITController.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$ITController = new ITController();
$categories = $ITController->getCategories();

$AdminController = new AdminController();
$roles = $AdminController->getitRoles();

$mainCategories = [];
$subCategories = [];

foreach ($categories as $category) {
    if (empty($category['parent_id'])) {
        $mainCategories[] = $category;
    } else {
        $subCategories[$category['parent_id']][] = $category; // Group subcategories by parent_id
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $parentId = empty($_POST['parent_id']) ? null : $_POST['parent_id'];
    $itroleId = empty($_POST['itrole_id']) ? null : $_POST['itrole_id'];
    $requiresApproval = isset($_POST['requires_approval']) ? 1 : 0; // Handle requires approval

    if ($id) {
        $AdminController->updateCategory($id, $name, $parentId, $itroleId, $requiresApproval);
    } else {
        $AdminController->addCategory($name, $parentId, $itroleId, $requiresApproval);
    }

    header('Location: categories.php'); 
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Tables</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom CSS to enhance spacing and layout */
        .main-category-section {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .main-category-section h3 {
            color: #007bff;
            font-weight: bold;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .btn-success {
            font-size: 0.9rem;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn-primary i {
            margin-right: 5px;
        }
        .modal-content {
            padding: 20px;
        }
        .mb-3 {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Main Categories and Their Subcategories</h2>

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="" data-name="" data-parent-id="" data-itrole-id="">
                <i class="fas fa-plus"></i> Add Main Category
            </button>
        </div>

        <?php if (!empty($mainCategories)): ?>
            <?php foreach ($mainCategories as $mainCategory): ?>
                <div class="main-category-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><?php echo htmlspecialchars($mainCategory['name']); ?></h3>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="" data-name="" data-parent-id="<?php echo htmlspecialchars($mainCategory['id']); ?>" data-itrole-id="">
                            <i class="fas fa-plus"></i> Add Subcategory
                        </button>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subcategory Name</th>
                                <th>IT Personnel</th>
                                <th># Tickets</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($subCategories[$mainCategory['id']])): ?>
                                <?php foreach ($subCategories[$mainCategory['id']] as $subCategory): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subCategory['id']); ?></td>
                                        <td><?php echo htmlspecialchars($subCategory['name']); ?></td>
                                        <td><?php echo htmlspecialchars($AdminController->getRoleNameById($subCategory['itrole_id'])); ?></td>
                                        <td><?php echo $ITController->countTickets('', '', '', $subCategory['id']); ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="<?php echo htmlspecialchars($subCategory['id']); ?>" data-name="<?php echo htmlspecialchars($subCategory['name']); ?>" data-parent-id="<?php echo htmlspecialchars($subCategory['parent_id']); ?>" data-itrole-id="<?php echo htmlspecialchars($subCategory['itrole_id']); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No subcategories found for this main category.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No main categories found.</p>
        <?php endif; ?>
    </div>
    <!-- Edit Category Modal -->
   <!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="post" action="categories.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="categoryId">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>
                    <div class="mb-3" id="parentCategoryDiv">
                        <label for="categoryParentId" class="form-label">Parent Category</label>
                        <select class="form-control" name="parent_id" id="categoryParentId">
                            <option value="">None</option>
                            <?php foreach ($mainCategories as $mainCategory): ?>
                                <option value="<?php echo htmlspecialchars($mainCategory['id']); ?>"><?php echo htmlspecialchars($mainCategory['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="itroleDiv">
                        <label for="itroleId" class="form-label">IT Role</label>
                        <select class="form-control" name="itrole_id" id="itroleId">
                            <option value="">None</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['id']); ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="requiresApproval" class="form-label">Requires Approval</label><br>
                        <div>
                            <input type="radio" id="approvalYes" name="requires_approval" value="1">
                            <label for="approvalYes">Yes</label>
                        </div>
                        <div>
                            <input type="radio" id="approvalNo" name="requires_approval" value="0">
                            <label for="approvalNo">No</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="save">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script>
                var editCategoryModal = document.getElementById('editCategoryModal');
        editCategoryModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var parentId = button.getAttribute('data-parent-id');
            var itroleId = button.getAttribute('data-itrole-id');
            var requiresApproval = button.getAttribute('data-requires-approval'); // New attribute for approval status

            var modalTitle = editCategoryModal.querySelector('.modal-title');
            var modalBodyInputId = editCategoryModal.querySelector('#categoryId');
            var modalBodyInputName = editCategoryModal.querySelector('#categoryName');
            var modalBodyInputParentId = editCategoryModal.querySelector('#categoryParentId');
            var modalBodyInputItroleId = editCategoryModal.querySelector('#itroleId');
            var modalBodyRequiresApprovalYes = editCategoryModal.querySelector('#approvalYes');
            var modalBodyRequiresApprovalNo = editCategoryModal.querySelector('#approvalNo');

            if (id) {
                modalTitle.textContent = 'Edit Category ' + name;
                modalBodyInputId.value = id;
                modalBodyInputName.value = name;
                modalBodyInputParentId.value = parentId;
                modalBodyInputItroleId.value = itroleId;
                if (requiresApproval === '1') {
                    modalBodyRequiresApprovalYes.checked = true;
                } else {
                    modalBodyRequiresApprovalNo.checked = true;
                }
            } else {
                modalTitle.textContent = 'Add Category';
                modalBodyInputId.value = '';
                modalBodyInputName.value = '';
                modalBodyInputParentId.value = parentId === 'default' ? '' : parentId;
                modalBodyInputItroleId.value = '';
                modalBodyRequiresApprovalYes.checked = false;
                modalBodyRequiresApprovalNo.checked = false;
            }
        });

    </script>
</body>
</html>
