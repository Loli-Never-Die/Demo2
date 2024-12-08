<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

// Lấy sản phẩm mới nhất
$stmt = $conn->prepare("
    SELECT p.*, c.CategoryName 
    FROM Products p
    JOIN Categories c ON p.CategoryID = c.CategoryID
    ORDER BY p.CreatedDate DESC 
    LIMIT 8
");
$stmt->execute();
$newProducts = $stmt->fetchAll();

// Lấy sản phẩm giảm giá
$stmt = $conn->prepare("
    SELECT p.*, c.CategoryName 
    FROM Products p
    JOIN Categories c ON p.CategoryID = c.CategoryID
    WHERE p.OldPrice > p.Price 
    LIMIT 4
");
$stmt->execute();
$saleProducts = $stmt->fetchAll();

// Lấy tất cả danh mục
$stmt = $conn->prepare("SELECT * FROM Categories WHERE ParentCategoryID IS NULL");
$stmt->execute();
$categories = $stmt->fetchAll();

// Tổ chức dữ liệu
$categoryProducts = [];
foreach ($categories as $category) {
    // Lấy 4 sản phẩm mới nhất của mỗi danh mục
    $stmt = $conn->prepare("
        SELECT p.*, c.CategoryName 
        FROM Products p
        JOIN Categories c ON p.CategoryID = c.CategoryID
        WHERE p.CategoryID = ?
        ORDER BY p.CreatedDate DESC
        LIMIT 4
    ");
    $stmt->execute([$category['CategoryID']]);
    $products = $stmt->fetchAll();
    
    $categoryProducts[$category['CategoryID']] = [
        'category' => $category,
        'products' => $products
    ];
}

// Debug
echo "<!-- Số danh mục: " . count($categoryProducts) . " -->";
foreach ($categoryProducts as $catId => $data) {
    echo "<!-- Danh mục $catId: " . count($data['products']) . " sản phẩm -->";
}
?>

<!-- SECTION -->
<div class="section">
    <!-- container -->
    <div class="container">
        <!-- Sản phẩm mới -->
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h3 class="title">Sản phẩm mới</h3>
                </div>
                <div class="row">
                    <?php foreach($newProducts as $product): ?>
                    <div class="col-md-3">
                        <div class="product">
                            <div class="product-img">
                                <?php 
                                // Thêm dòng debug này
                                echo "<!-- Debug Image Path: " . htmlspecialchars($product['Image']) . " -->"; 
                                ?>
                                <img src="<?php echo htmlspecialchars($product['Image']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                                <?php if($product['OldPrice'] > $product['Price']): ?>
                                <div class="product-label">
                                    <span class="sale">-<?php echo round((($product['OldPrice'] - $product['Price'])/$product['OldPrice'])*100); ?>%</span>
                                </div>
                                <?php endif; ?>
                                <div class="product-btns">
                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['ProductID']; ?>)">
                                        <i class="fa fa-shopping-cart"></i>
                                    </button>
                                    <button class="add-to-cart-btn" onclick="addToWishlist(<?php echo $product['ProductID']; ?>)">
                                        <i class="fa fa-heart<?php 
                                            if(isset($_SESSION['UserID'])) {
                                                $stmt = $conn->prepare("SELECT 1 FROM Wishlist WHERE UserID = ? AND ProductID = ?");
                                                $stmt->execute([$_SESSION['UserID'], $product['ProductID']]);
                                                echo $stmt->fetch() ? '' : '-o';
                                            } else {
                                                echo '-o';
                                            }
                                        ?>"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-body">
                                <p class="product-category"><?php echo $product['CategoryName']; ?></p>
                                <h3 class="product-name"><a href="product.php?id=<?php echo $product['ProductID']; ?>"><?php echo $product['ProductName']; ?></a></h3>
                                <h4 class="product-price">
                                    <?php echo number_format($product['Price']); ?>đ 
                                    <?php if($product['OldPrice']): ?>
                                    <del class="product-old-price"><?php echo number_format($product['OldPrice']); ?>đ</del>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sản phẩm theo danh mục -->
        <?php foreach ($categoryProducts as $categoryData): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h3 class="title"><?php echo $categoryData['category']['CategoryName']; ?></h3>
                    <div class="section-nav">
                        <a href="store.php?category=<?php echo $categoryData['category']['CategoryID']; ?>" class="primary-btn">Xem tất cả</a>
                    </div>
                </div>
                <div class="row">
                    <?php foreach($categoryData['products'] as $product): ?>
                    <div class="col-md-3">
                        <div class="product">
                            <div class="product-img">
                                <?php 
                                // Thêm dòng debug này
                                echo "<!-- Debug Image Path: " . htmlspecialchars($product['Image']) . " -->"; 
                                ?>
                                <img src="<?php echo htmlspecialchars($product['Image']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                                <?php if($product['OldPrice'] > $product['Price']): ?>
                                <div class="product-label">
                                    <span class="sale">-<?php echo round((($product['OldPrice'] - $product['Price'])/$product['OldPrice'])*100); ?>%</span>
                                </div>
                                <?php endif; ?>
                                <div class="product-btns">
                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['ProductID']; ?>)">
                                        <i class="fa fa-shopping-cart"></i>
                                    </button>
                                    <button class="add-to-cart-btn" onclick="addToWishlist(<?php echo $product['ProductID']; ?>)">
                                        <i class="fa fa-heart<?php 
                                            if(isset($_SESSION['UserID'])) {
                                                $stmt = $conn->prepare("SELECT 1 FROM Wishlist WHERE UserID = ? AND ProductID = ?");
                                                $stmt->execute([$_SESSION['UserID'], $product['ProductID']]);
                                                echo $stmt->fetch() ? '' : '-o';
                                            } else {
                                                echo '-o';
                                            }
                                        ?>"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-body">
                                <p class="product-category"><?php echo $categoryData['category']['CategoryName']; ?></p>
                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo $product['ProductID']; ?>">
                                        <?php echo $product['ProductName']; ?>
                                    </a>
                                </h3>
                                <h4 class="product-price">
                                    <?php echo number_format($product['Price']); ?>đ 
                                    <?php if($product['OldPrice']): ?>
                                    <del class="product-old-price"><?php echo number_format($product['OldPrice']); ?>đ</del>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 

<!-- Thêm vào cuối file, trước </body> -->
<script>
function addToCart(productId) {
    $.ajax({
        url: 'ajax/add-to-cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: 1
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                // Cập nhật số lượng giỏ hàng trên header
                updateCartCount();
            } else {
                alert(response.message);
                if (response.message.includes('đăng nhập')) {
                    window.location.href = 'login.php';
                }
            }
        }
    });
}

function addToWishlist(productId) {
    $.ajax({
        url: 'ajax/add-to-wishlist.php',
        type: 'POST',
        data: {
            product_id: productId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                // Cập nhật icon trái tim
                var btn = $(`.add-to-wishlist[onclick="addToWishlist(${productId})"] i`);
                if (response.action === 'add') {
                    btn.removeClass('fa-heart-o').addClass('fa-heart');
                } else {
                    btn.removeClass('fa-heart').addClass('fa-heart-o');
                }
            } else {
                alert(response.message);
                if (response.message.includes('đăng nhập')) {
                    window.location.href = 'login.php';
                }
            }
        }
    });
}

function updateCartCount() {
    $.ajax({
        url: 'ajax/get-cart-count.php',
        type: 'GET',
        success: function(response) {
            $('.cart-count').text(response);
        }
    });
}
</script> 