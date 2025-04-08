<?php
session_start();

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
    h2 {text-align: center;}
    a {text-decoration: none;}
    form, li { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); margin-bottom: 10px; }
    input[type="text"], input[type="file"] { margin: 5px 0; padding: 8px; width: 100%; box-sizing: border-box; }
    button {text-transform: uppercase; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; font-size: 0.75em; padding: 10px; width: 100%;}
    button:hover { background: #666; }
    li button { background: #ccc; margin-left: 10px;}
    .btn {position: absolute; top: 0; right: 0; color: white; background: #666; padding: 10px 20px;}
    .btn:hover {background-color: #000;}
    #bannersContainer {max-width: 50%; margin: 20px auto; background-color: #fff; padding: 40px; border-radius: 8px; padding: 30px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);}
    #addBanner {display: block; margin: 0 auto;}
    .image-preview { max-width: 100%; height: auto; display: block; margin-top: 10px; border: 1px solid #ccc; border-radius: 5px; }
    .drop-zone { border: 2px dashed #aaa; padding: 10px; text-align: center; color: #777; margin-top: 10px; cursor: pointer; }
    .drop-zone.dragover { background-color: #e0f7e0; border-color: #006847; }
    .image-preview {width: 75%; margin: 20px auto;}
    label {display: block; font-size: 0.9em; color: #666;}
    hr {margin: 20px 0 40px 0; border-color: #f1f1f1; color: #ccc;}
  </style>
</head>
<body>

<h2>Banner Manager</h2>
<div id="bannersContainer">
<button id="addBanner">Add New Banner</button>

</div>
<a href="logout.php" class="btn">Logout</a>
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("bannersContainer");

    // Fetch existing banners and populate the form
    fetch('../banners.json')
      .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
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
        console.error('Error loading banners:', error);
        alert('Error loading banners.');
      });

    function uploadImage(file, onSuccess, onError) {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('csrf_token', csrfToken);

      fetch('upload-image.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          onSuccess(data.url);
        } else {
          onError(data.message);
        }
      })
      .catch(err => {
        console.error('Upload error:', err);
        onError('Upload error');
      });
    }

    function createBannerForm(banner, index) {
      const div = document.createElement('div');
      div.className = "banner-form";
      const imageUrl = banner.image ? `${banner.image}` : '';
      div.innerHTML = `
        <hr>
        <label>Image URL: <input type="text" class="image" value="${imageUrl}"></label><br>
        <label>
          Upload Image:
          <input type="file" class="imageFile" accept="image/*">
        </label>
        <div class="drop-zone">Drag & Drop Image Here</div>
        <img class="image-preview" src="${imageUrl}" alt="Preview">
        <label class="filename-label"></label>
        <label>Title: <input type="text" class="title" value="${banner.title}"></label><br>
        <label>Subtitle: <input type="text" class="subtitle" value="${banner.subtitle}"></label><br>
        <label>Link: <input type="text" class="link" value="${banner.link}"></label><br>
        <label>Button Text: <input type="text" class="button_text" value="${banner.button_text}"></label><br>
        <button class="save-banner" data-index="${index}">Save</button>
        <button class="delete-banner" data-index="${index}">Delete</button>
      `;

      const fileInput = div.querySelector('.imageFile');
      const filenameLabel = div.querySelector('.filename-label');
      const preview = div.querySelector('.image-preview');
      const imageInput = div.querySelector('.image');
      const dropZone = div.querySelector('.drop-zone');

      function handleFileUpload(file) {
        if (!file) return;

        filenameLabel.textContent = file.name;

        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(file);

        uploadImage(file, (url) => {
          imageInput.value = url;
        }, (errorMessage) => {
          alert(errorMessage);
          filenameLabel.textContent = 'No file selected';
          preview.src = '';
          imageInput.value = '';
        });
      }

      fileInput.addEventListener('change', function () {
        handleFileUpload(this.files[0]);
      });

      dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
      });

      dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
      });

      dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
          fileInput.files = e.dataTransfer.files;
          handleFileUpload(file);
        } else {
          alert('Please upload a valid image file.');
        }
      });

      return div;
    }

    function collectBanners() {
      const forms = document.querySelectorAll(".banner-form");
      return Array.from(forms).map(form => {
        const image = form.querySelector(".image").value;
        const title = form.querySelector(".title").value;
        const subtitle = form.querySelector(".subtitle").value;
        const link = form.querySelector(".link").value;
        const button_text = form.querySelector(".button_text").value;

        // Validation to ensure title and link are not empty
        if (!title || !link) {
          alert("Title and Link are required fields.");
          return null;  // Return null to filter invalid entries
        }

        return { image, title, subtitle, link, button_text };
      }).filter(Boolean);  // Remove null values from the array
    }

    // Event delegation for Save and Delete buttons
    container.addEventListener('click', function(e) {
      if (e.target && e.target.matches('.save-banner')) {
        const index = e.target.dataset.index;
        saveBanner(index);
      } else if (e.target && e.target.matches('.delete-banner')) {
        const index = e.target.dataset.index;
        deleteBanner(index);
      }
    });

    function saveBanner(index) {
      const updatedBanners = collectBanners();
      if (updatedBanners.length === 0) return;  // If no valid banners, do not proceed.

      fetch("save-banners.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ csrf_token: csrfToken, banners: updatedBanners })
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) {
          alert("Banner saved!");
        } else {
          alert("Failed to save: " + response.message);
        }
      });
    }

    function deleteBanner(index) {
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
    }

    // Add a new banner form when clicking the "Add Banner" button
    document.getElementById("addBanner").addEventListener("click", () => {
      const newBanner = { image: "", title: "", subtitle: "", link: "", button_text: "" };
      const newForm = createBannerForm(newBanner);
      container.appendChild(newForm);
    });
  });
</script>



</body>
</html>
