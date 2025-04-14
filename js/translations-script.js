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
    loadTranslations(savedLang);

    const switcher = document.getElementById("languageSwitcher");
    if (switcher) {
      switcher.value = savedLang;
      switcher.addEventListener("change", (e) => {
        setLanguage(e.target.value);
      });
    }
  });
