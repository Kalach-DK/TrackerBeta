<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Tracker</title>
  <link rel="stylesheet" href="/tracker/assets/main.css">
  
  <style>
/* Generic button styling */
.button {
  background: #007bff;
  border: none;
  color: white;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}
.button:hover {
  background: #0056b3;
}
.button.small-ghost {
  background: transparent;
  border: 1px solid #aaa;
  color: #333;
  padding: 5px 10px;
  font-size: 14px;
}

/* Modal styling */
.modal {
  display: none; /* Hidden by default */
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  justify-content: center;
  align-items: center;
  z-index: 999;
}
.modal.active {
  display: flex;
}
.panel {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 420px;
  max-width: 95%;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}
.panel h3 {
  margin-top: 0;
  margin-bottom: 16px;
}
.input {
  width: 100%;
  margin-bottom: 10px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 6px;
}
</style>

</head>
<body>
<div class="tracker-app">