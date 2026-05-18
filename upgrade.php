<?php
// ============================================================
// upgrade.php - CineLog Pro Upgrade (Dummy Payment Simulator)
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';

$uq = mysqli_prepare($conn, "SELECT username, is_pro FROM users WHERE id = ?");
mysqli_stmt_bind_param($uq, "i", $user_id);
mysqli_stmt_execute($uq);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($uq));

// Handle "payment" submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app this is where you'd call Stripe/PayPal.
    // Here we just set is_pro = 1 to simulate success.
    $upd = mysqli_prepare($conn, "UPDATE users SET is_pro = 1 WHERE id = ?");
    mysqli_stmt_bind_param($upd, "i", $user_id);
    mysqli_stmt_execute($upd);

    if (mysqli_stmt_affected_rows($upd) > 0) {
        $action = "Upgraded to CineLog Pro";
        $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
        mysqli_stmt_bind_param($log, "is", $user_id, $action);
        mysqli_stmt_execute($log);

        $success        = "Welcome to CineLog Pro! Your membership is now active. 🎉";
        $user['is_pro'] = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Pro - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="main-container">
<div class="upgrade-wrapper">

    <?php if ($user['is_pro'] && !$success): ?>
        <!-- Already Pro -->
        <div class="card text-center">
            <div style="font-size:5rem;margin-bottom:var(--space-md);">⭐</div>
            <h2>You're already a Pro member!</h2>
            <p class="text-muted">Thank you for supporting CineLog.</p>
            <a href="dashboard.php" class="btn btn-primary mt-md">Back to My Diary</a>
        </div>

    <?php elseif ($success): ?>
        <!-- Payment Success -->
        <div class="card text-center">
            <div style="font-size:5rem;margin-bottom:var(--space-md);">🎉</div>
            <h2>Payment Successful!</h2>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        </div>

    <?php else: ?>
        <!-- Upgrade Sales Card -->
        <div class="card text-center">
            <div style="font-size:5rem;margin-bottom:var(--space-sm);">🌟</div>
            <h2>Upgrade to CineLog Pro</h2>
            <div class="price-tag">$4.99<span style="font-size:1.8rem;font-weight:400;"> / month</span></div>
            <p class="text-muted">Cancel anytime. No hidden fees.</p>

            <ul class="feature-list">
                <li>Unlimited movie entries</li>
                <li>Export your diary as PDF</li>
                <li>Advanced stats &amp; charts</li>
                <li>Priority customer support</li>
                <li>Pro badge on your profile</li>
                <li>Ad-free experience</li>
            </ul>
        </div>

        <!-- Dummy Payment Form -->
        <div class="card">
            <h3>💳 Payment Details</h3>
            <div class="alert alert-warning">
                DEMO MODE: This is a simulated checkout. No real money is charged.
            </div>

            <form method="POST" action="upgrade.php"
                  id="payment-form" onsubmit="return processPayment()">

                <div class="form-group">
                    <label for="card_name">Name on Card</label>
                    <input type="text" id="card_name" name="card_name"
                           placeholder="John Doe" autocomplete="cc-name" required>
                </div>

                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number"
                           placeholder="1234 5678 9012 3456"
                           maxlength="19"
                           autocomplete="cc-number" required>
                </div>

                <!-- Two columns: expiry and CVV side by side -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry">Expiry (MM/YY)</label>
                        <input type="text" id="expiry" name="expiry"
                               placeholder="MM/YY" maxlength="5"
                               autocomplete="cc-exp" required>
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv"
                               placeholder="123" maxlength="3"
                               autocomplete="cc-csc" required>
                    </div>
                </div>

                <button type="submit" id="pay-btn" class="btn btn-success btn-block btn-lg">
                    Pay $4.99 &amp; Upgrade Now
                </button>
            </form>
        </div>

    <?php endif; ?>

</div>
</div>

<button class="scroll-top-btn" id="scroll-top-btn" aria-label="Scroll to top">↑</button>

<script>
    // Auto-format card number with spaces every 4 digits
    document.addEventListener('DOMContentLoaded', function() {
        var cardInput = document.getElementById('card_number');
        if (!cardInput) return;

        cardInput.addEventListener('input', function() {
            // Remove anything that isn't a digit
            var digits    = this.value.replace(/\D/g, '').slice(0, 16);
            // Split into groups of 4 and join with spaces
            var formatted = digits.match(/.{1,4}/g);
            this.value    = formatted ? formatted.join(' ') : '';
        });

        // Auto-format expiry as MM/YY
        var expiryInput = document.getElementById('expiry');
        if (expiryInput) {
            expiryInput.addEventListener('input', function() {
                var val = this.value.replace(/\D/g, '').slice(0, 4);
                if (val.length >= 3) {
                    this.value = val.slice(0, 2) + '/' + val.slice(2);
                } else {
                    this.value = val;
                }
            });
        }
    });

    // Fake payment processing with a "Processing..." delay
    function processPayment() {
        var cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        var expiry     = document.getElementById('expiry').value;
        var cvv        = document.getElementById('cvv').value;
        var cardName   = document.getElementById('card_name').value.trim();

        if (cardName === '') {
            alert('Please enter the name on your card.');
            return false;
        }
        if (cardNumber.length < 16) {
            alert('Please enter a valid 16-digit card number.');
            return false;
        }
        if (expiry.length < 5) {
            alert('Please enter a valid expiry date (MM/YY).');
            return false;
        }
        if (cvv.length < 3) {
            alert('Please enter a valid 3-digit CVV.');
            return false;
        }

        // Show processing state
        var btn       = document.getElementById('pay-btn');
        btn.textContent = '⏳ Processing...';
        btn.disabled    = true;

        // Submit the form after a 2-second fake delay
        setTimeout(function() {
            document.getElementById('payment-form').submit();
        }, 2000);

        return false; // Prevent immediate submit; setTimeout handles it
    }
</script>

</body>
</html>