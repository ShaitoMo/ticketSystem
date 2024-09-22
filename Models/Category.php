<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $parent_id;
    public $created_at;
    public $role_id;
    public $requires_approval;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new category
    public function create($name, $parent_id = null, $requires_approval = 0) {
        $query = "INSERT INTO " . $this->table_name . " (name, parent_id, requires_approval) VALUES (:name, :parent_id, :requires_approval)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":parent_id", $parent_id);
        $stmt->bindParam(":requires_approval", $requires_approval);
        return $stmt->execute();
    }

    // Read all categories
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all main categories
    public function getMainCategories() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE parent_id IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all subcategories
    public function getSubCategories() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE parent_id IS NOT NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a category's name by its ID
    public function getCategoryNameById($category_id) {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $category_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['name'] ?? null;
    }

    // Get a category's ID by its name
    public function getCategoryIdByName($category_name) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $category_name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id'] ?? null;
    }

    // Update a category
    public function updateCategory($id, $name, $parentId, $itroleId, $requires_approval) {
        $sql = "UPDATE categories SET name = :name, parent_id = :parent_id, itrole_id = :itrole_id, requires_approval = :requires_approval WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
        $stmt->bindValue(':itrole_id', $itroleId, PDO::PARAM_INT);
        $stmt->bindValue(':requires_approval', $requires_approval, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Add a new category
    public function addCategory($name, $parentId = null, $itroleId = null, $requires_approval = 0) {
        $sql = "INSERT INTO categories (name, parent_id, itrole_id, requires_approval) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name, $parentId, $itroleId, $requires_approval]);
    }

    // Get subcategories by main category ID
    public function getSubCategoriesByMainCategory($category_id) {
        $query = "SELECT * FROM categories WHERE parent_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get team members by parent category ID
    public function getTeamMembers($parent_id) {
        $categoryQuery = "SELECT id FROM " . $this->table_name . " WHERE parent_id = :parent_id";
        $categoryStmt = $this->conn->prepare($categoryQuery);
        $categoryStmt->bindParam(":parent_id", $parent_id);
        $categoryStmt->execute();
        $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($categories)) {
            return [];
        }

        $inQuery = implode(',', array_fill(0, count($categories), '?'));
        $roleQuery = "SELECT DISTINCT id FROM roles WHERE id IN (SELECT itrole_id FROM categories WHERE id IN ($inQuery))";
        $roleStmt = $this->conn->prepare($roleQuery);
        $roleStmt->execute($categories);
        $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($roles)) {
            return [];
        }

        $inQueryRoles = implode(',', array_fill(0, count($roles), '?'));
        $userQuery = "SELECT DISTINCT u.id, u.first_name, u.email
                      FROM users u
                      JOIN it_roles ir ON u.id = ir.user_id
                      WHERE ir.role_id IN ($inQueryRoles)";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->execute($roles);

        return $userStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get roles by parent category ID
    public function getRolesByParentId($parent_id) {
        $query = "SELECT r.id, r.name FROM roles r
                  INNER JOIN categories c ON r.id = c.itrole_id
                  WHERE c.parent_id = :parent_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":parent_id", $parent_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get parent ID by category ID
    public function getParentIdByCategoryId($category_id) {
        $query = "SELECT parent_id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['parent_id'] ?? null;
    }

   
    public function requiresApproval($category_id) {
        $query = "SELECT requires_approval FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $category_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        return !empty($row['requires_approval']);
    }

    public function getCategoryNamesByRoleId($role_id) {

        $query = "SELECT name FROM " . $this->table_name . " WHERE itrole_id = :role_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role_id", $role_id, PDO::PARAM_INT);
        $stmt->execute();
        

        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $categories; }
    
    
}

?>
