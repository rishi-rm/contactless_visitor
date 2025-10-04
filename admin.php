<?php
// admin.php

include 'db.php';
session_start();

$admin_password = "admin123"; // change this

// --- Admin Login ---
if (!isset($_SESSION['admin_logged_in'])) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['password'] === $admin_password) {
    $_SESSION['admin_logged_in'] = true;
  } else {
    echo '<!DOCTYPE html><html><head><title>Admin Login</title>
    <style>
      body {font-family:Arial;background:#fce7f3;display:flex;justify-content:center;align-items:center;height:100vh;}
      form {background:#fff;padding:2rem;border-radius:15px;box-shadow:0 0 15px rgba(0,0,0,0.1);}
      input {padding:10px;border:1px solid #ddd;border-radius:10px;width:200px;}
      button {padding:10px 20px;background:#ec4899;color:white;border:none;border-radius:10px;margin-top:10px;cursor:pointer;}
    </style></head><body>
    <form method="POST"><h3>Admin Login</h3><input type="password" name="password" placeholder="Enter Password" required><br><button type="submit">Login</button></form>
    </body></html>';
    exit;
  }
}

// --- Fetch Visitor Data ---
$totalVisitors = 0;
$todayVisitors = 0;
$recentVisitors = [];

try {
  // Total visitors
  $result = $conn->query("SELECT COUNT(*) AS total FROM visitors");
  $totalVisitors = $result->fetch_assoc()['total'] ?? 0;

  // Today's visitors
  $today = date('Y-m-d');
  $result = $conn->query("SELECT COUNT(*) AS today FROM visitors WHERE DATE(checkin_time)='$today'");
  $todayVisitors = $result->fetch_assoc()['today'] ?? 0;

  // Recent 5 visitors
  $recentVisitors = $conn->query("SELECT * FROM visitors ORDER BY checkin_time DESC LIMIT 5");
} catch (Exception $e) {
  die("Error fetching visitors: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Visitor Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { box-sizing: border-box; }
    .gradient-bg {
      background: linear-gradient(45deg, #fce7f3, #fbcfe8, #f9a8d4, #f472b6, #ec4899, #be185d);
      background-size: 400% 400%;
      animation: gradientFlow 12s ease-in-out infinite;
    }
    @keyframes gradientFlow {
      0%, 100% { background-position: 0% 50%; }
      25% { background-position: 100% 50%; }
      50% { background-position: 100% 100%; }
      75% { background-position: 0% 100%; }
    }
    .card-glow {
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1), 
                  0 0 0 1px rgba(255, 255, 255, 0.3),
                  inset 0 1px 0 rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(15px);
    }
    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15); }
    .title-gradient {
      background: linear-gradient(135deg, #1e293b, #475569);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .btn-primary {
      background: linear-gradient(135deg, #ec4899, #be185d);
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #be185d, #9d174d);
      transform: translateY(-1px);
      box-shadow: 0 10px 20px -5px rgba(190, 24, 93, 0.4);
    }
    .pulse-dot { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
  </style>
</head>
<body class="gradient-bg min-h-screen">

<!-- Header -->
<header class="bg-white/10 backdrop-blur-md border-b border-white/20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center py-4">
      <div class="flex items-center">
        <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl mr-3 shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
      </div>
      <div class="flex items-center space-x-4">
        <div class="flex items-center text-white">
          <div class="w-2 h-2 bg-green-400 rounded-full pulse-dot mr-2"></div>
          <span class="text-sm">System Online</span>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <!-- Stats Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card rounded-2xl p-6">
      <p class="text-sm font-medium text-slate-600">Total Visitors</p>
      <p class="text-3xl font-bold title-gradient"><?= $totalVisitors ?></p>
    </div>

    <div class="stat-card rounded-2xl p-6">
      <p class="text-sm font-medium text-slate-600">Check-ins Today</p>
      <p class="text-3xl font-bold title-gradient"><?= $todayVisitors ?></p>
    </div>

    <div class="stat-card rounded-2xl p-6">
      <p class="text-sm font-medium text-slate-600">Recent Visitors</p>
      <p class="text-3xl font-bold title-gradient">
        <?= is_object($recentVisitors) ? $recentVisitors->num_rows : 0 ?>
      </p>
    </div>

    <div class="stat-card rounded-2xl p-6">
      <p class="text-sm font-medium text-slate-600">Database Status</p>
      <p class="text-3xl font-bold title-gradient text-green-500">Connected</p>
    </div>
  </div>

  <!-- Recent Visitors List -->
  <div class="bg-white/90 backdrop-blur-sm rounded-3xl p-6 card-glow">
    <h2 class="text-xl font-bold title-gradient mb-6">Recent Visitors</h2>
    <div class="space-y-4">
      <?php if ($recentVisitors && $recentVisitors->num_rows > 0): ?>
        <?php while ($row = $recentVisitors->fetch_assoc()): ?>
          <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-pink-600 rounded-full flex items-center justify-center text-white font-medium">
                <?= strtoupper(substr($row['name'], 0, 2)) ?>
              </div>
              <div class="ml-3">
                <p class="font-medium text-slate-900"><?= htmlspecialchars($row['name']) ?></p>
                <p class="text-sm text-slate-500">
                  <?= htmlspecialchars($row['email']) ?> — <?= date('d M h:i A', strtotime($row['checkin_time'])) ?>
                </p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-slate-600">No recent visitors found.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>
