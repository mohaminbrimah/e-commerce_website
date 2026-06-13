<?php
/**
 * MAB Shop - Admin Product Management
 * Add, edit, delete products and manage inventory
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitizeInput($_POST['name'] ?? '');
        $slug = createSlug($name);
        $data = [
            (int)$_POST['category_id'], (int)($_POST['brand_id'] ?: null) ?: null,
            $name, $slug, sanitizeInput($_POST['description'] ?? ''),
            sanitizeInput($_POST['short_description'] ?? ''),
            (float)$_POST['price'], (float)($_POST['compare_price'] ?: 0) ?: null,
            sanitizeInput($_POST['sku'] ?? ''), (int)$_POST['stock_quantity'],
            sanitizeInput($_POST['color'] ?? ''), sanitizeInput($_POST['size'] ?? ''),
            !empty($_POST['is_featured']) ? 1 : 0,
        ];

        if ($action === 'add') {
            $stmt = $pdo->prepare('INSERT INTO products (category_id, brand_id, name, slug, description, short_description, price, compare_price, sku, stock_quantity, color, size, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute($data);
            setFlash('success', 'Product added.');
        } else {
            $data[] = $id;
            $stmt = $pdo->prepare('UPDATE products SET category_id=?, brand_id=?, name=?, slug=?, description=?, short_description=?, price=?, compare_price=?, sku=?, stock_quantity=?, color=?, size=?, is_featured=? WHERE id=?');
            $stmt->execute($data);
            setFlash('success', 'Product updated.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([(int)$_POST['id']]);
        setFlash('success', 'Product deactivated.');
    }
    redirect(url('admin/products.php'));
}

$products = $pdo->query('SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.is_active = 1 ORDER BY p.created_at DESC')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1')->fetchAll();
$brands = $pdo->query('SELECT id, name FROM brands')->fetchAll();

$pageTitle = 'Products';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold">Products</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()"><i class="bi bi-plus"></i> Add Product</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Sold</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): $stock = getStockStatus((int)$p['stock_quantity']); ?>
            <tr>
                <td><strong><?= e($p['name']) ?></strong><br><small class="text-muted"><?= e($p['sku']) ?></small></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= formatPrice((float)$p['price']) ?></td>
                <td><span class="badge bg-<?= $stock['class'] ?>"><?= $p['stock_quantity'] ?></span></td>
                <td><?= (int)$p['sold_count'] ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick='editProduct(<?= json_encode($p) ?>)'>Edit</button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Deactivate product?')"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content"><?= csrfField() ?>
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Add Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Name</label><input type="text" name="name" id="pName" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">SKU</label><input type="text" name="sku" id="pSku" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Category</label><select name="category_id" id="pCategory" class="form-select" required><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Brand</label><select name="brand_id" id="pBrand" class="form-select"><option value="">None</option><?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Price</label><input type="number" step="0.01" name="price" id="pPrice" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Compare Price</label><input type="number" step="0.01" name="compare_price" id="pCompare" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Stock</label><input type="number" name="stock_quantity" id="pStock" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Color</label><input type="text" name="color" id="pColor" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Size</label><input type="text" name="size" id="pSize" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Featured</label><div class="form-check mt-2"><input type="checkbox" name="is_featured" id="pFeatured" class="form-check-input" value="1"></div></div>
                    <div class="col-12"><label class="form-label">Short Description</label><input type="text" name="short_description" id="pShort" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="pDesc" class="form-control" rows="3"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<script>
function resetForm() { document.getElementById('formAction').value='add'; document.getElementById('modalTitle').textContent='Add Product'; document.getElementById('productId').value=''; }
function editProduct(p) {
    document.getElementById('formAction').value='edit'; document.getElementById('modalTitle').textContent='Edit Product';
    document.getElementById('productId').value=p.id; document.getElementById('pName').value=p.name;
    document.getElementById('pSku').value=p.sku||''; document.getElementById('pCategory').value=p.category_id;
    document.getElementById('pBrand').value=p.brand_id||''; document.getElementById('pPrice').value=p.price;
    document.getElementById('pCompare').value=p.compare_price||''; document.getElementById('pStock').value=p.stock_quantity;
    document.getElementById('pColor').value=p.color||''; document.getElementById('pSize').value=p.size||'';
    document.getElementById('pFeatured').checked=p.is_featured==1; document.getElementById('pShort').value=p.short_description||'';
    document.getElementById('pDesc').value=p.description||'';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
