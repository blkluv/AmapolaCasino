<?php
session_start(); // Start the session to access session data, including CSRF token

// Optional: Check if the user is logged in or redirect to the login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>" />
  <title>Banner Manager</title>
  <style>
    body {font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0;}
    h2 { color: #006847; text-align: center;}
    a {text-decoration: none;}
    form, li { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
    input { margin: 5px 0; padding: 8px; width: 100%; box-sizing: border-box; }
    button { background: #006847; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; font-size: 0.75em; padding: 10px 20px;}
    button:hover { background: #004d34; }
    li button { background: #ccc; margin-left: 10px;}
    .btn {position: absolute; top: 0; right: 0; color: white; background: #666; padding: 10px 20px;}
    .btn:hover {background-color: #000;}
    #bannersContainer {max-width: 800px; margin: 20px auto; background-color: #fff; padding: 40px; border-radius: 8px; padding: 30px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);}
    #addBanner {display: block; margin: 0 auto;}
  </style>
</head>
<body>

<h2>Banner Manager</h2>
<button id="addBanner">+ Add New Banner</button>
<div id="bannersContainer"></div>
<a href="logout.php" class="btn">Logout</a>
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("bannersContainer");

    fetch('banners.json')
      .then(res => {
        if (!res.ok) {
          throw new Error('Network response was not ok');
        }
        return res.json();
      })
      .then(data => {
        const banners = data.banners;

        if (Array.isArray(banners)) {
          banners.forEach((banner, index) => {
            container.appendChild(createBannerForm(banner, index));
          });
        } else {
          throw new Error('Data.banners is not an array');
        }
      })
      .catch(error => {
        console.error('Error fetching or parsing the JSON:', error);
        alert('Error loading banners. Please try again later.');
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

    function collectBanners() {
      const forms = document.querySelectorAll(".banner-form");
      return Array.from(forms).map(form => ({
        image: form.querySelector(".image").value,
        title: form.querySelector(".title").value,
        subtitle: form.querySelector(".subtitle").value,
        link: form.querySelector(".link").value,
        button_text: form.querySelector(".button_text").value
      }));
    }

    window.saveBanner = function(index) {
      const updatedBanners = collectBanners();
      fetch("save-banners.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ csrf_token: csrfToken, banners: updatedBanners })
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) alert("Banner saved!");
        else alert("Failed to save: " + response.message);
      });
    };

    window.deleteBanner = function(index) {
      if (!confirm("Are you sure you want to delete this banner?")) return;
      const updatedBanners = collectBanners();
      updatedBanners.splice(index, 1);

      fetch("save-banners.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ csrf_token: csrfToken, banners: updatedBanners })
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) {
          document.querySelectorAll(".banner-form")[index].remove();
        } else {
          alert("Failed to delete: " + response.message);
        }
      });
    };

    document.getElementById("addBanner").addEventListener("click", () => {
      const newBanner = { image: "", title: "", subtitle: "", link: "", button_text: "" };
      const newForm = createBannerForm(newBanner);
      container.appendChild(newForm);
    });
  });
</script>
</body>
</html>
