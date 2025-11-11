const themeToggle = document.getElementById("theme-toggle");
const aElement = document.querySelectorAll("a");

themeToggle.addEventListener("click", () => {
  const body = document.body;
  body.classList.toggle("light");
  body.classList.toggle("dark");
  localStorage.setItem(
    "theme",
    body.classList.contains("light") ? "light" : "dark"
  );

  if (localStorage.getItem("theme") === "light") {
    body.style.backgroundColor = "#ffffff";
    body.style.color = "#000000";
    aElement.forEach((element) => {
      element.style.color = "#000000";
    });
  } else {
    body.style.backgroundColor = "#131313";
    body.style.color = "#f3f3f3";
    aElement.forEach((element) => {
      element.style.color = "#f3f3f3";
    });
  }
});

window.onload = () => {
  const theme = localStorage.getItem("theme");

  if (theme === "light") {
    document.body.classList.add("light");
    document.body.style.backgroundColor = "#ffffff";
    document.body.style.color = "#000000";
    aElement.forEach((element) => {
      element.style.color = "#000000";
    });
  } else if (theme === "dark") {
    document.body.classList.add("dark");
    document.body.style.backgroundColor = "#131313";
    document.body.style.color = "#f3f3f3";
    aElement.forEach((element) => {
      element.style.color = "#f3f3f3";
    });
  } else {
    const body = document.body;
    body.classList.toggle("light");
    body.classList.toggle("dark");
    aElement.forEach((element) => {
      element.style.color = "#000000";
    });
    localStorage.setItem(
      "theme",
      body.classList.contains("light") ? "light" : "dark"
    );
  }
};
