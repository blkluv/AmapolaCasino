  // Sanitize helper for banners
  function sanitize(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Load banners depending on language
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

  // Declare translations object
  var translations = {};

  // Set language (applies translations)
  function setLanguage(lang) {
    console.log("setLanguage called with:", lang);
  
    if (lang === 'en') {
      localStorage.setItem("lang", "en");
      location.reload(); // English is hardcoded in HTML
      return;
    }
  
    // If translations are not loaded yet, load them first
    if (!translations[lang]) {
      console.log("Translations not loaded, fetching now...");
      loadTranslations(lang); // this will call setLanguage again once ready
      return;
    }
  
    console.log("Applying translations for:", lang);
  
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
    loadBanners(lang);
  }
  
  

  // Load translations JSON file
  async function loadTranslations(lang) {
    if (lang === 'en') {
      loadBanners('en');
      return;
    }
  
    try {
      const response = await fetch("./translations.json");
      const data = await response.json();
      translations[lang] = data[lang];
      console.log("Translations loaded for", lang);
      setLanguage(lang); // now safe to apply
    } catch (error) {
      console.error("Error loading translations:", error);
    }
  }
  
  

  // On page load
  document.addEventListener("DOMContentLoaded", () => {
    const savedLang = localStorage.getItem("lang") || "en";
    loadTranslations(savedLang);
  
    const switcher = document.getElementById("languageSwitcher");
    if (switcher) {
      switcher.value = savedLang;
      switcher.addEventListener("change", (e) => {
        const newLang = e.target.value;
        console.log("Language switched to:", newLang); // Debugging line
        setLanguage(newLang);
      });
    } else {
      console.log("Language switcher not found!"); // Debugging line
    }
  });
  
