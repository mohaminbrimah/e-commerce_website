<?php
/**
 * MAB Shop - Product Model
 * Handles product queries, search, filtering, and recommendations
 */

declare(strict_types=1);

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Get products with filters, sorting, and pagination
     */
    public function getProducts(array $filters = [], string $sort = 'newest', int $page = 1, int $perPage = PRODUCTS_PER_PAGE): array
    {
        $where = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['brand_id'])) {
            $where[] = 'p.brand_id = ?';
            $params[] = $filters['brand_id'];
        }
        if (!empty($filters['min_price'])) {
            $where[] = 'p.price >= ?';
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = 'p.price <= ?';
            $params[] = $filters['max_price'];
        }
        if (!empty($filters['color'])) {
            $where[] = 'p.color = ?';
            $params[] = $filters['color'];
        }
        if (!empty($filters['size'])) {
            $where[] = 'p.size = ?';
            $params[] = $filters['size'];
        }
        if (!empty($filters['min_rating'])) {
            $where[] = 'p.rating_avg >= ?';
            $params[] = $filters['min_rating'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $orderBy = match ($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'rating'     => 'p.rating_avg DESC',
            'popular'    => 'p.sold_count DESC',
            default      => 'p.created_at DESC',
        };

        $whereClause = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM products p WHERE {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $pagination = paginate($total, $perPage, $page);

        $sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'products'   => $stmt->fetchAll(),
            'pagination' => $pagination,
        ];
    }

    /**
     * Get single product by slug with images and specs
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.slug = ? AND p.is_active = 1');
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $imgStmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, is_primary DESC');
        $imgStmt->execute([$product['id']]);
        $product['images'] = $imgStmt->fetchAll();

        $specStmt = $this->db->prepare('SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order');
        $specStmt->execute([$product['id']]);
        $product['specifications'] = $specStmt->fetchAll();

        // Increment view count
        $this->db->prepare('UPDATE products SET view_count = view_count + 1 WHERE id = ?')->execute([$product['id']]);
        trackRecentlyViewed((int)$product['id']);

        return $product;
    }

    /**
     * Autocomplete search suggestions
     */
    public function searchSuggestions(string $query, int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, price FROM products WHERE is_active = 1 AND name LIKE ? ORDER BY sold_count DESC LIMIT ?');
        $stmt->execute(['%' . $query . '%', $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Natural language search parser (AI-ready)
     */
    public function naturalLanguageSearch(string $query): array
    {
        $filters = [];
        $lower = strtolower($query);

        // Extract price limit: "under GH₵300" or "under 300"
        if (preg_match('/under\s*(?:gh[₵c]?\s*)?(\d+)/i', $query, $m)) {
            $filters['max_price'] = (float)$m[1];
        }

        // Extract color keywords
        $colors = ['black', 'white', 'blue', 'red', 'grey', 'gray', 'green', 'brown'];
        foreach ($colors as $color) {
            if (str_contains($lower, $color)) {
                $filters['color'] = ucfirst($color === 'gray' ? 'grey' : $color);
                break;
            }
        }

        // Extract category keywords
        if (str_contains($lower, 'sneaker') || str_contains($lower, 'shoe')) {
            $cat = $this->db->query("SELECT id FROM categories WHERE slug = 'sneakers'")->fetch();
            if ($cat) $filters['category_id'] = $cat['id'];
        }

        // Remaining text as search term
        $searchText = preg_replace('/show me|find|search|under\s*\d+|gh[₵c]?\s*\d+/i', '', $query);
        $searchText = trim($searchText);
        if ($searchText) {
            $filters['search'] = $searchText;
        }

        return $this->getProducts($filters);
    }

    /**
     * Get related/similar products
     */
    public function getRelated(int $productId, string $type = 'similar', int $limit = 4): array
    {
        $stmt = $this->db->prepare('SELECT p.*, pi.image_path AS image
            FROM product_bundles pb
            JOIN products p ON pb.related_product_id = p.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE pb.product_id = ? AND pb.bundle_type = ? AND p.is_active = 1
            LIMIT ?');
        $stmt->execute([$productId, $type, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get featured products for homepage
     */
    public function getFeatured(int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT p.*, pi.image_path AS image
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE p.is_active = 1 AND p.is_featured = 1
            ORDER BY p.sold_count DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get recently viewed products
     */
    public function getRecentlyViewed(int $limit = 6): array
    {
        $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
        $sessionId = $userId ? null : getGuestSessionId();

        if ($userId) {
            $stmt = $this->db->prepare('SELECT p.*, pi.image_path AS image FROM recently_viewed rv
                JOIN products p ON rv.product_id = p.id
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE rv.user_id = ? ORDER BY rv.viewed_at DESC LIMIT ?');
            $stmt->execute([$userId, $limit]);
        } else {
            $stmt = $this->db->prepare('SELECT p.*, pi.image_path AS image FROM recently_viewed rv
                JOIN products p ON rv.product_id = p.id
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE rv.session_id = ? ORDER BY rv.viewed_at DESC LIMIT ?');
            $stmt->execute([$sessionId, $limit]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get approved reviews for product
     */
    public function getReviews(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT r.*, u.first_name, u.last_name FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = "approved"
            ORDER BY r.created_at DESC');
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get filter options (brands, colors, sizes)
     */
    public function getFilterOptions(): array
    {
        return [
            'brands' => $this->db->query('SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name')->fetchAll(),
            'colors' => $this->db->query('SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND is_active = 1 ORDER BY color')->fetchAll(PDO::FETCH_COLUMN),
            'sizes'  => $this->db->query('SELECT DISTINCT size FROM products WHERE size IS NOT NULL AND is_active = 1 ORDER BY size')->fetchAll(PDO::FETCH_COLUMN),
            'categories' => $this->db->query('SELECT id, name, slug, parent_id FROM categories WHERE is_active = 1 ORDER BY sort_order')->fetchAll(),
        ];
    }
}
