document.addEventListener("DOMContentLoaded", function () {
    fetch("banners.json")
        .then(response => response.json())
        .then(banners => {
            const bannerWrapper = document.getElementById("bannerWrapper");

            banners.forEach(banner => {
                const slide = document.createElement("div");
                slide.classList.add("swiper-slide");
                slide.innerHTML = `
                    <div class="banner" style="background-image: url('${banner.image}')">
                        <div class="banner-content">
                            <h2>${banner.title}</h2>
                            <p>${banner.subtitle}</p>
                            <a href="${banner.link}" class="btn">${banner.button_text}</a>
                        </div>
                    </div>
                `;
                bannerWrapper.appendChild(slide);
            });

            new Swiper(".mySwiper", {
                loop: true,
                pagination: { el: ".swiper-pagination", clickable: true },
                navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
                autoplay: { delay: 5000 }
            });
        })
        .catch(error => console.error("Error loading banners:", error));
});
