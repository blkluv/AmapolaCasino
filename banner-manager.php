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
    h2 { color: #006847; }
    form, li { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
    input { margin: 5px 0; padding: 8px; width: 100%; box-sizing: border-box; }
    button { background: #006847; color: #fff; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #004d34; }
    li button { background: #ccc; margin-left: 10px; }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body onload="promptPassword()">
<a href="logout.php">Logout</a>

  <h2>Banner Manager</h2>

  <form id="bannerForm">
    <input type="text" id="image" placeholder="Image URL" required />
    <input type="text" id="title" placeholder="Title" required />
    <input type="text" id="subtitle" placeholder="Subtitle" required />
    <input type="text" id="link" placeholder="Link" required />
    <input type="text" id="button_text" placeholder="Button Text" required />
    <button type="submit">Add Banner</button>
  </form>

  <ul id="bannerList"></ul>

  <script>
    const PASSWORD_HASH = "5f4dcc3b5aa765d61d8327deb882cf99"; // "password"
    function hash(str) { return CryptoJS.MD5(str).toString(); }

    function promptPassword() {
      const input = prompt("Enter password:");
      if (hash(input) !== PASSWORD_HASH) {
        alert("Access denied.");
        document.body.innerHTML = "";
      }
    }

    let banners = [];

    document.addEventListener("DOMContentLoaded", () => {
      fetch("banners.json")
        .then(response => response.json())
        .then(data => {
          banners = data;
          renderBannerList();
        });

      document.getElementById("bannerForm").addEventListener("submit", defaultSubmit);
    });

    function defaultSubmit(e) {
      e.preventDefault();
      const newBanner = {
        image: image.value,
        title: title.value,
        subtitle: subtitle.value,
        link: link.value,
        button_text: button_text.value
      };
      banners.push(newBanner);
      renderBannerList();
      saveToServer();
      this.reset();
    }

    function renderBannerList() {
      const list = document.getElementById("bannerList");
      list.innerHTML = "";
      banners.forEach((b, index) => {
        const li = document.createElement("li");
        li.innerHTML = `
          <strong>${b.title}</strong> - ${b.subtitle}
          <button onclick="editBanner(${index})">Edit</button>
          <button onclick="deleteBanner(${index})">Delete</button>
        `;
        list.appendChild(li);
      });
    }

    function editBanner(index) {
      const b = banners[index];
      image.value = b.image;
      title.value = b.title;
      subtitle.value = b.subtitle;
      link.value = b.link;
      button_text.value = b.button_text;

      document.getElementById("bannerForm").onsubmit = function (e) {
        e.preventDefault();
        banners[index] = {
          image: image.value,
          title: title.value,
          subtitle: subtitle.value,
          link: link.value,
          button_text: button_text.value
        };
        renderBannerList();
        saveToServer();
        this.reset();
        this.onsubmit = defaultSubmit;
      };
    }

    function deleteBanner(index) {
      if (confirm("Are you sure?")) {
        banners.splice(index, 1);
        renderBannerList();
        saveToServer();
      }
    }

    function saveToServer() {
      fetch("save-banners.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(banners)
      })
      .then(res => res.ok ? alert("Changes saved!") : alert("Error saving file."))
      .catch(err => alert("Server error."));
    }
  </script>
</body>
</html>
