<?php
// vex_order_form.php (UPDATED FOR WASMER)

ini_set('display_errors', 0); // Sa live server, mas magandang 0 ito para hindi makita ng user ang errors
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/header.php';
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

require_once __DIR__ . '/db_connect.php';

$alert = '';
$itemName = $_GET['item'] ?? 'Unknown Item';
$itemPrice = 0;
$itemMedia = '';

$stmt = $pdo->prepare("SELECT price, media FROM store WHERE name=? LIMIT 1");
$stmt->execute([$itemName]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if ($item) {
    $itemPrice = (float)$item['price'];
    $itemMedia = $item['media'];
}

$isOrderSuccessful = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $size = $_POST['size'];
    $qty = (int)$_POST['quantity'];
    $payment = $_POST['payment'];
    $total = $itemPrice * $qty;

    if (empty($name) || empty($contact) || empty($address) || $qty < 1) {
        $alert = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    } else {
        $proofFilePath = '';
        $proofFileName = '';

        // --- FIXED: Handling file uploads for ephemeral filesystems ---
        // Kunin lang ang temporary path ng file para i-attach sa email
        // Hindi na natin ito isasave sa server's local disk
        if (!empty($_FILES['proof']['name']) && $_FILES['proof']['error'] == UPLOAD_ERR_OK) {
            $proofFilePath = $_FILES['proof']['tmp_name'];
            $proofFileName = $_FILES['proof']['name'];
        }

        try {
            // Ituloy ang pag-save sa database, pero ang 'proof_file' ay maaaring pangalan lang o null
            $stmt = $pdo->prepare("INSERT INTO store_orders (item_name, item_price, customer_name, contact, address, size, quantity, total, payment_method, proof_file, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$itemName, $itemPrice, $name, $contact, $address, $size, $qty, $total, $payment, $proofFileName ?: null, 'Pending']);
            
            // Send email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            
            // --- FIXED: Using Wasmer Secrets for security ---
            $mail->Username = getenv('SMTP_USER');
            $mail->Password = getenv('SMTP_PASS');

            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('vxbot.auto@gmail.com', 'VX Orders');
            $mail->addAddress($contact);
            $mail->addCC('vxbot.auto@gmail.com'); // Important: para matanggap mo ang kopya na may attachment

            // --- FIXED: Attach the file directly from its temporary location ---
            if ($proofFilePath && $proofFileName) {
                $mail->addAttachment($proofFilePath, $proofFileName);
            }

            $mail->isHTML(true);
            $mail->Subject = "VX Jersey Order Confirmation";
            $mail->Body = "<h2>Thank you for your order, $name!</h2><p><strong>Item:</strong> $itemName</p><p><strong>Size:</strong> $size</p><p><strong>Quantity:</strong> $qty</p><p><strong>Total:</strong> ₱" . number_format($total) . "</p><p><strong>Address:</strong> $address</p><p><strong>Payment Method:</strong> $payment</p><p>Join our Discord: <a href='https://discord.gg/vexillum'>https://discord.gg/vexillum</a></p>";
            $mail->send();

            $isOrderSuccessful = true;

        } catch (Exception $e) {
            $alert = "<div class='alert alert-danger'>Order received but there was an issue sending the email: {$mail->ErrorInfo}</div>";
        }
    }
}
?>

<style>
/* --- BAGONG CSS PARA SA ORDER FORM --- */
:root { 
    --vx-red: #ff2a2a; 
    --vx-dark: #0a0a0a; 
    --vx-dark-secondary: #141414; 
    --vx-text: #e0e0e0;
    --vx-text-muted: #888;
}

.order-form-page {
    background-color: var(--vx-dark);
    color: var(--vx-text);
    padding: 120px 1rem 4rem 1rem; /* Padding para sa fixed header */
}

.order-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
    max-width: 1100px;
    margin: 0 auto;
}

@media (min-width: 992px) {
    .order-container {
        grid-template-columns: 1fr 1.5fr; /* Preview sa kaliwa, form sa kanan */
    }
}

/* Item Preview Section */
.item-preview {
    background-color: var(--vx-dark-secondary);
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    position: sticky; /* Para hindi gumalaw pag nag-scroll */
    top: 120px;
}
.item-preview h2 {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    margin-bottom: 0.5rem;
}
.item-preview .price {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 1.5rem;
}
.media-preview {
    width: 100%;
    height: 400px;
    background-color: #000;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.media-preview video, .media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center;
    border-radius: 8px;
}

/* Form Styling */
.form-wrapper h1 {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    text-align: center;
    margin-bottom: 2rem;
}
.form-section {
    background-color: var(--vx-dark-secondary);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
.form-section h4 {
    font-family: 'Xirod', sans-serif;
    color: #fff;
    border-bottom: 2px solid var(--vx-red);
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}
.form-control, .form-select {
    background-color: var(--vx-dark);
    color: var(--vx-text);
    border: 1px solid #333;
    border-radius: 5px;
    padding: 0.8rem 1rem;
}
.form-control:focus, .form-select:focus {
    background-color: var(--vx-dark);
    color: var(--vx-text);
    border-color: var(--vx-red);
    box-shadow: 0 0 10px rgba(255, 42, 42, 0.2);
}
.payment-info {
    background-color: var(--vx-dark);
    border-radius: 5px;
    padding: 1rem;
    border-left: 3px solid var(--vx-red);
}
.total-price-display {
    text-align: right;
    font-size: 1.5rem;
    font-weight: 700;
}
.submit-btn {
    background: var(--vx-red);
    color: #fff;
    font-family: 'Xirod', sans-serif;
    border: none;
    padding: 1rem 2rem;
    border-radius: 5px;
    width: 100%;
    text-transform: uppercase;
    font-size: 1.2rem;
    transition: background-color 0.3s ease;
}
.submit-btn:hover { background-color: #fff; color: var(--vx-red); }

/* Success Modal */
.modal-bg { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.85); display:flex; justify-content:center; align-items:center; z-index:9999; }
.success-modal { 
    background:var(--vx-dark-secondary); 
    border:2px solid var(--vx-red); 
    padding:2rem 3rem; 
    border-radius:12px; 
    text-align:center; 
    max-width:400px; 
    color: var(--vx-text); 
}
.success-modal h2 { 
    color:var(--vx-red); 
    margin-bottom:1rem; 
    font-family: 'Xirod', sans-serif; 
    font-size: clamp(1.0rem, 3vw, 1.5rem); /* Responsive font size */
    word-wrap: break-word;
}
.success-modal p {
    font-size: clamp(0.7rem, 1.5vw, 1.0rem); /* Responsive font size */
    word-wrap: break-word;
}
.success-modal button { background:var(--vx-red); color:#fff; padding:.6rem 1.5rem; border:none; border-radius:6px; cursor:pointer; margin-top:1rem; font-family: 'Titillium Web', sans-serif; font-weight: 700; }
</style>

<?php if ($isOrderSuccessful): ?>
<div class="modal-bg" id="modalSuccess">
  <div class="success-modal">
    <h2><i class="fas fa-check-circle"></i> YOUR ORDER HAS BEEN SUCCESSFULLY SUBMITTED!</h2>
    <p>Please check your email for confirmation. We will contact you shortly.</p>
    <a href="store.php" class="btn btn-danger">Back to Store</a>
  </div>
</div>
<?php endif; ?>

<main class="order-form-page">
    <div class="order-container">
        <!-- Left Side: Item Preview -->
        <aside class="item-preview">
            <div class="media-preview">
                <?php 
                    $media_extension = pathinfo($itemMedia, PATHINFO_EXTENSION);
                    $is_video = in_array(strtolower($media_extension), ['mp4', 'webm', 'mov']);
                ?>
                <?php if ($is_video): ?>
                    <video src="<?= htmlspecialchars($itemMedia) ?>" autoplay muted loop playsinline></video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($itemMedia) ?>" alt="<?= htmlspecialchars($itemName) ?>">
                <?php endif; ?>
            </div>
            <h2><?= htmlspecialchars($itemName) ?></h2>
            <p class="price" id="price-per-item" data-price="<?= $itemPrice ?>">₱<?= number_format($itemPrice, 2) ?></p>
            <a href="store.php" class="btn btn-outline-secondary w-100">← Back to Store</a>
        </aside>

        <!-- Right Side: Form -->
        <div class="form-wrapper">
            <h1>Order Form</h1>
            <?= $alert ?>
            <form method="POST" enctype="multipart/form-data">
                
                <!-- Shipping Details -->
                <div class="form-section">
                    <h4>1. Shipping Details</h4>
                    <div class="mb-3"><input type="text" class="form-control" name="name" placeholder="Full Name*" required></div>
                    <div class="mb-3"><input type="text" class="form-control" name="contact" placeholder="Contact Number / Email*" required></div>
                    <div class="mb-3"><input type="text" class="form-control" name="address" placeholder="Full Shipping Address*" required></div>
                </div>

                <!-- Order Details -->
                <div class="form-section">
                    <h4>2. Order Details</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <select name="size" class="form-select" required>
                                <option value="" selected disabled>Select Size*</option>
                                <?php foreach (['XS','S','M','L','XL','XXL'] as $s): ?>
                                <option value="<?= $s ?>"><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="number" name="quantity" class="form-control" min="1" value="1" id="quantity" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="form-section">
                    <h4>3. Payment</h4>
                    <div class="mb-3">
                        <select name="payment" id="payment-method" class="form-select" onchange="showPaymentInfo()" required>
                            <option value="" selected disabled>Select Payment Method*</option>
                            <option value="GCash">GCash</option>
                            <option value="PayMaya">PayMaya</option>
                            <option value="UnionBank">UnionBank</option>
                        </select>
                    </div>
                    <div id="gcash-info" class="payment-info" style="display:none;"><p><strong>GCash:</strong> John VX | 0917 123 4567</p></div>
                    <div id="paymaya-info" class="payment-info" style="display:none;"><p><strong>PayMaya:</strong> Jane VX | 0998 765 4321</p></div>
                    <div id="unionbank-info" class="payment-info" style="display:none;"><p><strong>UnionBank:</strong> VX Team | 1234 5678 9012</p></div>
                    <div class="mt-3"><label class="form-label">Proof of Payment (Optional)</label><input type="file" class="form-control" name="proof" accept="image/*"></div>
                </div>
                
                <!-- Total and Submit -->
                <div class="d-flex justify-content-between align-items-center my-4">
                    <h4 class="m-0">TOTAL:</h4>
                    <div class="total-price-display" id="total-price">₱<?= number_format($itemPrice, 2) ?></div>
                </div>

                <button type="submit" class="submit-btn">Submit Order</button>
            </form>
        </div>
    </div>
</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
function showPaymentInfo() {
    const method = document.getElementById('payment-method').value;
    document.getElementById('gcash-info').style.display = method === 'GCash' ? 'block' : 'none';
    document.getElementById('paymaya-info').style.display = method === 'PayMaya' ? 'block' : 'none';
    document.getElementById('unionbank-info').style.display = method === 'UnionBank' ? 'block' : 'none';
}

function calculateTotal() {
    const pricePerItem = parseFloat(document.getElementById('price-per-item').dataset.price);
    const quantity = parseInt(document.getElementById('quantity').value);
    const total = pricePerItem * (quantity > 0 ? quantity : 1);
    document.getElementById('total-price').textContent = '₱' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

document.getElementById('quantity').addEventListener('input', calculateTotal);
</script>

<?php include 'includes/footer.php'; ?>
