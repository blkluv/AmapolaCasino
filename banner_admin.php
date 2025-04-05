<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Banner Manager</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f8f8f8; }
    h2 { color: #006847; text-align: center;}
    a {text-decoration: none;}
    form, li { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
    input { margin: 5px 0; padding: 8px; width: 100%; box-sizing: border-box; }
    button { background: #006847; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; font-size: 0.75em; padding: 10px 20px;}
    button:hover { background: #004d34; }
    li button { background: #ccc; margin-left: 10px;}
    .btn {position: absolute; top: 10px; right: 10px; color: white; background: #333; padding: 10px 20px; border-radius: 4px; font-size: 0.75em}
    .btn:hover {background-color: #000;}
    #bannersContainer {max-width: 800px; margin: 0 auto;}
    #addBanner {display: block; margin: 0 auto;}
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body onload="promptPassword()">

<h2>Banner Manager</h2>
<button id="addBanner">+ Add New Banner</button>
<div id="bannersContainer"></div>
<a href="logout.php" class="btn">Logout</a>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("bannersContainer");

    fetch('banners.json')
      .then(res => res.json())
      .then(data => {
        data.forEach((banner, index) => {
          container.appendChild(createBannerForm(banner, index));
        });
      });

    function createBannerForm(banner, index) {
      const div = document.createElement('div');
      div.className = "banner-form";
      div.innerHTML = `
        <hr>
        <label>Image URL: <input type="text" class="image" value="${banner.image}"></label><br>
        <label>Title: <input type="text" class="title" value="${banner.title}"></label><br>
        <label>Subtitle: <input type="text" class="subtitle" value="${banner.subtitle}"></label><br>
        <label>Link: <input type="text" class="link" value="${banner.link}"></label><br>
        <label>Button Text: <input type="text" class="button_text" value="${banner.button_text}"></label><br>
        <button onclick="saveBanner(${index})">💾 Save</button>
        <button onclick="deleteBanner(${index})">🗑 Delete</button>
      `;
      return div;
    }

    window.saveBanner = function(index) {
      const forms = document.querySelectorAll(".banner-form");
      const updatedBanners = Array.from(forms).map(form => ({
        image: form.querySelector(".image").value,
        title: form.querySelector(".title").value,
        subtitle: form.querySelector(".subtitle").value,
        link: form.querySelector(".link").value,
        button_text: form.querySelector(".button_text").value
      }));

      fetch("save-banners.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(updatedBanners)
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) alert("Banner saved!");
        else alert("Failed to save");
      });
    };

    window.deleteBanner = function(index) {
      if (!confirm("Are you sure you want to delete this banner?")) return;

      const forms = document.querySelectorAll(".banner-form");
      const updatedBanners = Array.from(forms).map(form => ({
        image: form.querySelector(".image").value,
        title: form.querySelector(".title").value,
        subtitle: form.querySelector(".subtitle").value,
        link: form.querySelector(".link").value,
        button_text: form.querySelector(".button_text").value
      }));

      updatedBanners.splice(index, 1);

      fetch("save-banners.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(updatedBanners)
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) location.reload();
        else alert("Failed to delete");
      });
    };

    document.getElementById("addBanner").addEventListener("click", () => {
      const newBanner = {
        image: "",
        title: "",
        subtitle: "",
        link: "",
        button_text: ""
      };
      container.appendChild(createBannerForm(newBanner));
    });
  });
</script>

</body>
</html>
