const themeToggle = document.getElementById("theme-toggle");

themeToggle.addEventListener("click", () => {
  const body = document.body;
  const currentTheme = body.classList.contains("light") ? "dark" : "light";

  body.classList.remove("light", "dark");
  body.classList.add(currentTheme);

  localStorage.setItem("theme", currentTheme);
  applyTheme(currentTheme);
});

function applyTheme(theme) {
  const root = document.documentElement;

  if (theme === "light") {
    root.style.setProperty("--bg-color", "#ffffff");
    root.style.setProperty("--text-color", "#000000");
    root.style.setProperty("--link-color", "#0119f7");
  } else {
    root.style.setProperty("--bg-color", "#131313");
    root.style.setProperty("--text-color", "#f3f3f3");
    root.style.setProperty("--link-color", "#f3f3f3");
  }
}

window.onload = () => {
  const theme = localStorage.getItem("theme") || "light";
  document.body.classList.add(theme);
  applyTheme(theme);
};
