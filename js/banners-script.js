function sanitize(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
  
  function loadBanners(lang) {
    const bannerWrapper = document.getElementById("bannerWrapper");
    if (!bannerWrapper) return;
  
    bannerWrapper.innerHTML = ""; // Clear previous banners
  
    const jsonFile = lang === 'es' ? './banners-es.json' : './banners.json';
  
    fetch(jsonFile)
      .then(response => {
        if (!response.ok) throw new Error(`Failed to fetch ${jsonFile}`);
        return response.json();
      })
      .then(data => {
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
  
        new Swiper(".mySwiper", {
          loop: true,
          pagination: { el: ".swiper-pagination", clickable: true },
          navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
          autoplay: { delay: 7500 }
        });
      })
      .catch(error => console.error("Error loading banners:", error));
  }
  
  // Language setup
  let translations = {};
  
  function setLanguage(lang) {
    if (!translations[lang]) return;
  
    document.querySelectorAll("[data-i18n]").forEach(el => {
      const key = el.getAttribute("data-i18n");
      if (translations[lang][key]) el.textContent = translations[lang][key];
    });
    document.querySelectorAll("[data-i18n-placeholder]").forEach(el => {
      const key = el.getAttribute("data-i18n-placeholder");
      if (translations[lang][key]) el.setAttribute("placeholder", translations[lang][key]);
    });
    document.querySelectorAll("[data-i18n-value]").forEach(el => {
      const key = el.getAttribute("data-i18n-value");
      if (translations[lang][key]) el.setAttribute("value", translations[lang][key]);
    });
  
    localStorage.setItem("lang", lang);
    loadBanners(lang); // Load corresponding banners
  }
  
  async function loadTranslations(lang) {
    try {
      const response = await fetch("./translations.json");
      const data = await response.json();
      translations = data;
      setLanguage(lang);
    } catch (error) {
      console.error("Error loading translations:", error);
    }
  }
  
  document.addEventListener("DOMContentLoaded", () => {
    const savedLang = localStorage.getItem("lang") || "en";
    loadTranslations(savedLang); // This will also call loadBanners()
  
    const switcher = document.getElementById("languageSwitcher");
    if (switcher) {
      switcher.value = savedLang;
      switcher.addEventListener("change", (e) => {
        const newLang = e.target.value;
        setLanguage(newLang);
      });
    }
  });
  