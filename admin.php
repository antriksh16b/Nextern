<?php
require_once __DIR__ . '/includes/functions.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nextern Owner Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
</head>
<body class="admin-body">
  <main class="admin-wrap">
    <section class="admin-card" id="adminLoginCard">
      <p class="section-label">Owner Access</p>
      <h1 class="section-title">Dashboard <em>Login</em></h1>
      <p class="admin-sub">Only website owner can access meeting and user data.</p>
      <form id="ownerLoginForm" class="admin-form">
        <label class="admin-label" for="ownerUsername">Username</label>
        <input id="ownerUsername" name="username" class="admin-input" type="text" required>
        <label class="admin-label" for="ownerPassword">Password</label>
        <input id="ownerPassword" name="password" class="admin-input" type="password" required>
        <button type="submit" class="btn-primary admin-btn">Login</button>
      </form>
      <p id="ownerLoginStatus" class="admin-status"></p>
      <a href="index.php" class="admin-back">Back to website</a>
    </section>
    <section class="admin-card admin-hidden" id="adminDashboard">
      <div class="admin-topbar">
        <div>
          <p class="section-label">Owner Panel</p>
          <h2 class="section-title">Meeting <em>Bookings</em></h2>
        </div>
        <div class="admin-actions">
          <a id="downloadCsvBtn" href="api.php?route=admin/export.csv" class="btn-primary">Download CSV</a>
          <button id="ownerLogoutBtn" class="btn-white" type="button">Logout</button>
        </div>
      </div>
      <section class="admin-settings-section" aria-label="Admin notification settings">
        <h3 class="day-details-title">Notification recipients</h3>
        <p class="admin-sub">Add the email addresses that should receive booking alerts. Separate multiple addresses with commas or line breaks.</p>
        <form id="adminSettingsForm" class="admin-form admin-settings-form">
          <label class="admin-label" for="adminEmailList">Admin emails</label>
          <textarea id="adminEmailList" name="adminEmails" class="admin-input admin-textarea" rows="4" placeholder="owner@example.com, team@example.com"></textarea>
          <button type="submit" class="btn-primary admin-btn">Save recipients</button>
        </form>
        <p id="adminSettingsStatus" class="admin-status"></p>
      </section>
      <div class="admin-calendar-section">
        <div class="admin-calendar-header">
          <button id="prevMonthBtn" class="admin-nav-btn" type="button">◀</button>
          <h3 id="calendarMonthYear" class="admin-calendar-title">January 2026</h3>
          <button id="nextMonthBtn" class="admin-nav-btn" type="button">▶</button>
        </div>
        <div class="admin-calendar-weekdays"><div class="weekday">Sun</div><div class="weekday">Mon</div><div class="weekday">Tue</div><div class="weekday">Wed</div><div class="weekday">Thu</div><div class="weekday">Fri</div><div class="weekday">Sat</div></div>
        <div id="calendarDays" class="admin-calendar-days"></div>
      </div>
      <div id="dayDetailsPanel" class="admin-day-details admin-hidden">
        <div class="day-details-header"><button id="closeDayDetails" class="close-btn" type="button">×</button><h3 id="dayDetailsTitle" class="day-details-title"></h3></div>
        <div id="dayDetailsContent" class="day-details-content"></div>
      </div>
      <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Business</th><th>Meeting Time</th><th>Type</th><th>Notes</th></tr></thead><tbody id="bookingRows"></tbody></table></div>
      <p id="adminDataStatus" class="admin-status"></p>
    </section>
  </main>
<script src="admin.js"></script>
</body>
</html>
