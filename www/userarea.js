const changeForm = document.querySelectorAll(".changeform");
const loginForm = document.querySelector("#form #login");
const registerForm = document.querySelector("#form #register");

changeForm.forEach((e) => {
    e.addEventListener("click", () => {
        loginForm.style.display = loginForm.style.display == "none" ? "flex" : "none";
        registerForm.style.display = registerForm.style.display == "none" ? "flex" : "none";
    });
});
