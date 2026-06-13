<?php
/**
 * MAB Shop - Admin Category Management
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $name = sanitizeInput($_POST['name'] ?? '');
    $slug = createSlug($name);
    if (($_POST['action'] ?? '') === 'add') {
        $pdo->prepare('INSERT INTO categories (name, slug, parent_id, description) VALUES (?,?,?,?)')->execute([
            $name, $slug, (int)($_POST['parent_id'] ?: null) ?: null, sanitizeInput($_POST['description'] ?? '')
        ]);
        setFlash('success', 'Category added.');
    }
    redirect(url('admin/categories.php'));
}

$categories = $pdo->query('SELECT c.*, p.name AS parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.sort_order')->fetchAll();

$pageTitle = 'Categories';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<div class="d-flex justify-content-between mb-4">
    <h1 class="fw-bold">Categories</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catModal">Add Category</button>
</div>

<table class="table card border-0 shadow-sm">
    <thead><tr><th>Name</th><th>Slug</th><th>Parent</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($categories as $c): ?>
    <tr><td><?= e($c['name']) ?></td><td><?= e($c['slug']) ?></td><td><?= e($c['parent_name'] ?? '-') ?></td><td><?= $c['is_active'] ? 'Active' : 'Inactive' ?></td></tr>
    <?php endforeach; ?></tbody>
</table>

<div class="modal fade" id="catModal"><div class="modal-dialog"><form method="POST" class="modal-content"><?= csrfField() ?><input type="hidden" name="action" value="add">
    <div class="modal-header"><h5>Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label>Parent Category</label><select name="parent_id" class="form-select"><option value="">None (Top Level)</option><?php foreach ($categories as $c): if(!$c['parent_id']): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endif; endforeach; ?></select></div>
        <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div>
<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
