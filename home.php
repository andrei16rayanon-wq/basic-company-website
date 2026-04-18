<?php
session_start();
require_once 'db_connect.php';

// Access Control
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit();
}

$userId   = $_SESSION['employee_id'];
$username = $_SESSION['username'];
$trade_message = "";

// Handle Buy/Sell Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $asset = $_POST['asset_name'];
    $type  = $_POST['action']; // 'BUY' or 'SELL'
    $qty   = floatval($_POST['quantity']);
    $price = floatval($_POST['current_price']);
    $total = $qty * $price;

    if ($qty > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO transactions 
                (employee_id, asset_name, transaction_type, quantity, price_at_time, total_value) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $asset, $type, $qty, $price, $total]);
            $trade_message = "ORDER EXECUTED: $type $qty $asset at $$price";
        } catch (PDOException $e) {
            $trade_message = "EXECUTION ERROR: " . $e->getMessage();
        }
    } else {
        $trade_message = "INVALID QUANTITY";
    }
}

// Fetch User Details
$stmtUser = $pdo->prepare("SELECT FirstName FROM employee WHERE Id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// Fetch last 10 trades for the Line Chart (increased from 5 for a better line)
$stmtTrades = $pdo->prepare("SELECT asset_name, total_value, created_at FROM transactions WHERE employee_id = ? ORDER BY created_at DESC LIMIT 10");
$stmtTrades->execute([$userId]);
$trades = $stmtTrades->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];
foreach (array_reverse($trades) as $t) {
    // Using time or index as label for the line chart
    $labels[] = date('H:i', strtotime($t['created_at']));
    $values[] = $t['total_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal.io | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #0b0e11; 
            --card: #1e2329; 
            --green: #0ecb81; 
            --red: #f6465d; 
            --border: #2b3139; 
            --text: #eaecef; 
            --gray: #848e9c; 
        }

        body { 
            font-family: 'Roboto', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            margin: 0; 
            display: flex; 
            flex-direction: column; 
            height: 100vh; 
            overflow: hidden;
        }

        /* Nav Bar */
        nav { 
            background: var(--card); 
            padding: 10px 20px; 
            border-bottom: 1px solid var(--border); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            z-index: 10;
        }
        .logo { color: var(--green); font-weight: bold; font-size: 20px; text-decoration: none; }
        .user-nav a { color: var(--text); text-decoration: none; margin-left: 20px; font-size: 14px; }

        /* Layout Grid */
        .main-container { 
            display: grid; 
            grid-template-columns: 1fr 350px; 
            flex: 1; 
            overflow: hidden; 
        }

        /* Left Side: Charts & Info */
        .chart-section { 
            padding: 20px; 
            border-right: 1px solid var(--border); 
            display: flex; 
            flex-direction: column; 
            gap: 20px;
            overflow-y: auto;
        }
        .price-ticker { 
            display: flex; 
            gap: 30px; 
            padding: 15px; 
            background: var(--card); 
            border-radius: 4px; 
            border: 1px solid var(--border); 
        }
        .ticker-item span { display: block; font-size: 11px; color: var(--gray); text-transform: uppercase; }
        .ticker-val { font-size: 18px; font-weight: bold; }

        .chart-container {
            flex: 1;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }

        /* Right Side: Trade Panel */
        .trade-panel { 
            background: var(--card); 
            padding: 25px; 
            display: flex; 
            flex-direction: column; 
            gap: 20px; 
        }
        .trade-tabs { display: flex; border-bottom: 1px solid var(--border); margin-bottom: 10px; }
        .tab { padding: 10px 20px; cursor: pointer; color: var(--green); font-weight: bold; border-bottom: 2px solid var(--green); }
        
        label { font-size: 12px; color: var(--gray); display: block; margin-bottom: 8px; }
        input { 
            background: #0b0e11; 
            border: 1px solid var(--border); 
            color: white; 
            padding: 12px; 
            border-radius: 4px; 
            font-size: 16px; 
            width: 100%; 
            box-sizing: border-box; 
        }

        .trade-buttons { display: flex; flex-direction: column; gap: 12px; margin-top: 10px; }
        .btn-buy { background: var(--green); color: black; border: none; padding: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-sell { background: var(--red); color: white; border: none; padding: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 16px; }
        
        .status-msg { font-size: 12px; padding: 12px; border-radius: 4px; text-align: center; background: rgba(14, 203, 129, 0.1); color: var(--green); border: 1px solid var(--green); }
        
        .footer-info { margin-top: auto; padding-top: 15px; font-size: 11px; color: var(--gray); border-top: 1px solid var(--border); }
    </style>
</head>
<body>

    <nav>
        <a href="home.php" class="logo"><i class="fa-solid fa-chart-line"></i> TERMINAL.IO</a>
        <div class="user-nav">
            <span style="color:var(--gray)">Operator: <b style="color:var(--text)"><?= htmlspecialchars($user['FirstName'] ?? $username) ?></b></span>
            <a href="profile.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
            <a href="logout.php" style="color:var(--red)"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </nav>

    <div class="main-container">
        <div class="chart-section">
            <div class="price-ticker">
                <div class="ticker-item">
                    <span>BTC / USDT Market Price</span>
                    <div class="ticker-val" id="btc-price">$64,231.50</div>
                </div>
                <div class="ticker-item">
                    <span>24h Change</span>
                    <div class="ticker-val" style="color:var(--green)">+2.45%</div>
                </div>
            </div>

            <div class="chart-container">
                <label>PORTFOLIO PERFORMANCE (RECENT TRANSACTIONS)</label>
                <div style="flex: 1; width: 100%;">
                    <canvas id="tradeChart"></canvas>
                </div>
            </div>

            <div class="footer-info">
                <p><i class="fa-solid fa-shield-halved"></i> Data Source: Binance Cloud API</p>
                <p><i class="fa-solid fa-clock"></i> Localized Terminal Time: <?= date('H:i:s') ?> UTC</p>
            </div>
        </div>

        <div class="trade-panel">
            <div class="trade-tabs">
                <div class="tab">Market Order</div>
            </div>

            <?php if($trade_message): ?>
                <div class="status-msg"><?= htmlspecialchars($trade_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="asset_name" value="BTC">
                <input type="hidden" id="hidden-price" name="current_price" value="64231.50">

                <div style="margin-bottom: 20px;">
                    <label>ORDER TYPE</label>
                    <input type="text" value="Market Execution" disabled style="opacity: 0.6;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label>AMOUNT (BTC)</label>
                    <input type="number" name="quantity" step="0.0001" placeholder="0.00" required>
                </div>

                <div class="trade-buttons">
                    <button type="submit" name="action" value="BUY" class="btn-buy">BUY / LONG</button>
                    <button type="submit" name="action" value="SELL" class="btn-sell">SELL / SHORT</button>
                </div>
            </form>

            <div style="margin-top: 20px; font-size: 12px; color: var(--gray);">
                <i class="fa-solid fa-circle-info"></i> All market orders are executed at the current ticker price.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- Line Chart Configuration ---
        const ctx = document.getElementById('tradeChart').getContext('2d');
        
        // Gradient fill for the area under the line
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(14, 203, 129, 0.3)');
        gradient.addColorStop(1, 'rgba(14, 203, 129, 0)');

        const tradeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Trade Value ($)',
                    data: <?= json_encode($values) ?>,
                    borderColor: '#0ecb81',
                    backgroundColor: gradient,
                    fill: true,
                    borderWidth: 3,
                    tension: 0.4, // Smoothing the line
                    pointRadius: 5,
                    pointBackgroundColor: '#0ecb81'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: false,
                        grid: { color: '#2b3139' },
                        ticks: { color: '#848e9c' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: '#848e9c' }
                    }
                }
            }
        });

        // --- Simulated Live Price ---
        const priceDisplay = document.getElementById('btc-price');
        const hiddenPriceInput = document.getElementById('hidden-price');
        
        setInterval(() => {
            let currentPrice = parseFloat(hiddenPriceInput.value);
            let volatility = (Math.random() - 0.5) * 45;
            currentPrice += volatility;
            
            hiddenPriceInput.value = currentPrice.toFixed(2);
            priceDisplay.textContent = "$" + currentPrice.toLocaleString(undefined, {minimumFractionDigits: 2});
        }, 3000);
    </script>
</body>
</html>