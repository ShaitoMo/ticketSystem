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
        $subCategories[] = $category;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $parentId = empty($_POST['parent_id']) ? null : $_POST['parent_id'];
    $itroleId = empty($_POST['itrole_id']) ? null : $_POST['itrole_id'];

    if ($id) {
        $AdminController->updateCategory($id, $name, $parentId, $itroleId);
    } else {
        $AdminController->addCategory($name, $parentId, $itroleId);
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
</head>
<body>
    <?php include_once 'header.php'; ?>
    <div class="container mt-5">
        <h2>Main Categories</h2>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="" data-name="" data-parent-id="" data-itrole-id="">
            <i class="fas fa-plus"></i> Main Category
        </button>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th># Tickets</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mainCategories)): ?>
                        <?php foreach ($mainCategories as $mainCategory): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mainCategory['id']); ?></td>
                                <td><?php echo htmlspecialchars($mainCategory['name']); ?></td>
                                <td><?php echo $ITController->countTickets('', '', '', $mainCategory['id']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="<?php echo htmlspecialchars($mainCategory['id']); ?>" data-name="<?php echo htmlspecialchars($mainCategory['name']); ?>" data-parent-id="" data-itrole-id="">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No main categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2>Sub Categories</h2>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="" data-name="" data-parent-id="default" data-itrole-id="">
            <i class="fas fa-plus"></i> Sub Category
        </button>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent ID</th>
                        <th>ItPersonal</th>
                        <th># Tickets</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subCategories)): ?>
                        <?php foreach ($subCategories as $subCategory): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subCategory['id']); ?></td>
                                <td><?php echo htmlspecialchars($subCategory['name']); ?></td>
                                <td><?php echo htmlspecialchars($subCategory['parent_id']); ?></td>
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
                            <td colspan="6">No sub categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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

            var modalTitle = editCategoryModal.querySelector('.modal-title');
            var modalBodyInputId = editCategoryModal.querySelector('#categoryId');
            var modalBodyInputName = editCategoryModal.querySelector('#categoryName');
            var modalBodyInputParentId = editCategoryModal.querySelector('#categoryParentId');
            var modalBodyInputItroleId = editCategoryModal.querySelector('#itroleId');

            if (id) {
                modalTitle.textContent = 'Edit Category ' + name;
                modalBodyInputId.value = id;
                modalBodyInputName.value = name;
                modalBodyInputParentId.value = parentId;
                modalBodyInputItroleId.value = itroleId;
                document.getElementById('parentCategoryDiv').style.display = parentId ? 'block' : 'none';
                document.getElementById('itroleDiv').style.display = parentId ? 'block' : 'none';
            } else {
                modalTitle.textContent = 'Add Category';
                modalBodyInputId.value = '';
                modalBodyInputName.value = '';
                modalBodyInputParentId.value = parentId === 'default' ? '' : parentId;
                modalBodyInputItroleId.value = '';
                document.getElementById('parentCategoryDiv').style.display = parentId === 'default' ? 'block' : 'none';
                document.getElementById('itroleDiv').style.display = parentId === 'default' ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>
