// Sanitize function outside of the event listener to make it reusable
function sanitize(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener("DOMContentLoaded", function () {
    // Load banners directly from JSON file
    fetch("/banners.json")
        .then(response => {
            if (!response.ok) {
                throw new Error("Failed to fetch banners.json");
            }
            return response.json();
        })
        .then(data => {
            const bannerWrapper = document.getElementById("bannerWrapper");
            const banners = data.banners;

            banners.forEach(banner => {
                const slide = document.createElement("div");
                slide.classList.add("swiper-slide");

                slide.innerHTML = `
                    <div class="banner" style="background-image: url('${sanitize(banner.image)}')">
                        <div class="banner-content">
                            <h2>${sanitize(banner.title)}</h2>
                            <p>${sanitize(banner.subtitle)}</p>
                            <a href="${sanitize(banner.link)}" class="btn btn-primary py-3 px-3">${sanitize(banner.button_text)}</a>
                        </div>
                    </div>
                `;
                bannerWrapper.appendChild(slide);
            });

            // Initialize Swiper
            new Swiper(".mySwiper", {
                loop: true,
                pagination: { el: ".swiper-pagination", clickable: true },
                navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
                autoplay: { delay: 7500 }
            });
        })
        .catch(error => console.error("Error loading banners:", error));

    // Save banners function (only used by admin panel)
    function saveBanners(bannersData) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch("/admin/save-banners.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                csrf_token: csrfToken,
                banners: bannersData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Banners updated successfully!');
            } else {
                alert('Failed to save: ' + data.message);
            }
        })
        .catch(error => console.error('Error saving banners:', error));
    }
});
