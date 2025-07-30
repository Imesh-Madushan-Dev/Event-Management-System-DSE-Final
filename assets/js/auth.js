// Authentication JavaScript

function showAlert(message, type) {
  const alertBox = document.createElement("div")
  alertBox.classList.add("alert", `alert-${type}`)
  alertBox.textContent = message
  document.body.appendChild(alertBox)

  setTimeout(() => {
    document.body.removeChild(alertBox)
  }, 3000)
}

function showLoading(button) {
  button.disabled = true
  button.textContent = "Loading..."
}

function hideLoading(button) {
  button.disabled = false
  button.textContent = "Submit"
}

document.addEventListener("DOMContentLoaded", () => {
  // Tab switching
  const userTab = document.getElementById("user-tab")
  const adminTab = document.getElementById("admin-tab")
  const userTypeInput = document.getElementById("user_type")

  if (userTab && adminTab && userTypeInput) {
    userTab.addEventListener("click", () => {
      userTab.classList.add("bg-white", "text-purple-600", "shadow-sm")
      userTab.classList.remove("text-gray-600")
      adminTab.classList.remove("bg-white", "text-purple-600", "shadow-sm")
      adminTab.classList.add("text-gray-600")
      userTypeInput.value = "user"
    })

    adminTab.addEventListener("click", () => {
      adminTab.classList.add("bg-white", "text-purple-600", "shadow-sm")
      adminTab.classList.remove("text-gray-600")
      userTab.classList.remove("bg-white", "text-purple-600", "shadow-sm")
      userTab.classList.add("text-gray-600")
      userTypeInput.value = "admin"
    })
  }

  // Password toggle
  const togglePassword = document.getElementById("toggle-password")
  const passwordInput = document.getElementById("password")

  if (togglePassword && passwordInput) {
    togglePassword.addEventListener("click", function () {
      const type = passwordInput.getAttribute("type") === "password" ? "text" : "password"
      passwordInput.setAttribute("type", type)

      const icon = this.querySelector("i")
      icon.classList.toggle("fa-eye")
      icon.classList.toggle("fa-eye-slash")
    })
  }

  // Confirm password toggle
  const toggleConfirmPassword = document.getElementById("toggle-confirm-password")
  const confirmPasswordInput = document.getElementById("confirm_password")

  if (toggleConfirmPassword && confirmPasswordInput) {
    toggleConfirmPassword.addEventListener("click", function () {
      const type = confirmPasswordInput.getAttribute("type") === "password" ? "text" : "password"
      confirmPasswordInput.setAttribute("type", type)

      const icon = this.querySelector("i")
      icon.classList.toggle("fa-eye")
      icon.classList.toggle("fa-eye-slash")
    })
  }

  // Form submission
  const loginForm = document.getElementById("login-form")
  const registerForm = document.getElementById("register-form")

  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin)
  }

  if (registerForm) {
    registerForm.addEventListener("submit", handleRegister)
  }
})

async function handleLogin(e) {
  e.preventDefault()

  const form = e.target
  const submitBtn = form.querySelector('button[type="submit"]')
  const formData = new FormData(form)

  // Validate form
  const email = formData.get("email")
  const password = formData.get("password")

  if (!email || !password) {
    showAlert("Please fill in all fields", "error")
    return
  }

  showLoading(submitBtn)

  try {
    const response = await fetch("backend/auth.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      showAlert("Login successful! Redirecting...", "success")
      setTimeout(() => {
        window.location.href = result.redirect
      }, 1000)
    } else {
      showAlert(result.message, "error")
    }
  } catch (error) {
    showAlert("An error occurred. Please try again.", "error")
  } finally {
    hideLoading(submitBtn)
  }
}

async function handleRegister(e) {
  e.preventDefault()

  const form = e.target
  const submitBtn = form.querySelector('button[type="submit"]')
  const formData = new FormData(form)

  // Validate form
  const name = formData.get("name")
  const email = formData.get("email")
  const password = formData.get("password")
  const confirmPassword = formData.get("confirm_password")

  if (!name || !email || !password || !confirmPassword) {
    showAlert("Please fill in all fields", "error")
    return
  }

  if (password !== confirmPassword) {
    showAlert("Passwords do not match", "error")
    return
  }

  if (password.length < 6) {
    showAlert("Password must be at least 6 characters long", "error")
    return
  }

  showLoading(submitBtn)

  try {
    const response = await fetch("backend/auth.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      showAlert("Registration successful! Please login.", "success")
      setTimeout(() => {
        window.location.href = "login.html"
      }, 2000)
    } else {
      showAlert(result.message, "error")
    }
  } catch (error) {
    showAlert("An error occurred. Please try again.", "error")
  } finally {
    hideLoading(submitBtn)
  }
}
