/**
 * NIBM Events — Authentication JavaScript
 * Handles: login form, register form, tab switching, password toggle
 */

"use strict";

/* ── Utility: Show Alert Toast ─────────────────── */
function showAlert(message, type = "info") {
  const container = document.getElementById("alert-container");
  if (!container) return;

  const icons = {
    success: "fas fa-check-circle",
    error: "fas fa-exclamation-circle",
    info: "fas fa-info-circle",
    warning: "fas fa-exclamation-triangle",
  };

  const alert = document.createElement("div");
  alert.className = `alert alert-${type} animate-slideUp`;
  alert.innerHTML = `
    <div class="flex items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <i class="${icons[type] || icons.info}"></i>
        <span>${message}</span>
      </div>
      <button onclick="this.closest('.alert').remove()" class="text-current opacity-60 hover:opacity-100 transition-opacity">
        <i class="fas fa-times text-xs"></i>
      </button>
    </div>
  `;

  container.appendChild(alert);
  setTimeout(() => alert.isConnected && alert.remove(), 5000);
}

/* ── Utility: Button Loading State ─────────────── */
function showLoading(button) {
  if (!button) return;
  button.dataset.originalText = button.innerHTML;
  button.classList.add("btn-loading");
  button.disabled = true;
}

function hideLoading(button) {
  if (!button) return;
  button.classList.remove("btn-loading");
  button.innerHTML = button.dataset.originalText || "Submit";
  button.disabled = false;
}

document.addEventListener("DOMContentLoaded", () => {
  initAuthTabs();
  initPasswordToggles();
  initFormHandlers();
});

/* ── Tab Switching (Student / Admin) ──────────── */
function initAuthTabs() {
  const userTab = document.getElementById("user-tab");
  const adminTab = document.getElementById("admin-tab");
  const userTypeInput = document.getElementById("user_type");
  if (!userTab || !adminTab || !userTypeInput) return;

  const setActive = (activeTab, inactiveTab, userType) => {
    activeTab.classList.add("bg-white", "text-purple-600", "shadow-sm");
    activeTab.classList.remove("text-gray-500");
    inactiveTab.classList.remove("bg-white", "text-purple-600", "shadow-sm");
    inactiveTab.classList.add("text-gray-500");
    userTypeInput.value = userType;
  };

  userTab.addEventListener("click", () => setActive(userTab, adminTab, "user"));
  adminTab.addEventListener("click", () =>
    setActive(adminTab, userTab, "admin"),
  );
}

/* ── Password Visibility Toggles ──────────────── */
function initPasswordToggles() {
  const toggles = [
    { btn: "toggle-password", input: "password" },
    { btn: "toggle-confirm-password", input: "confirm_password" },
  ];

  toggles.forEach(({ btn, input }) => {
    const button = document.getElementById(btn);
    const field = document.getElementById(input);
    if (!button || !field) return;

    button.addEventListener("click", () => {
      const isPassword = field.type === "password";
      field.type = isPassword ? "text" : "password";

      const icon = button.querySelector("i");
      icon.className = isPassword ? "fas fa-eye-slash" : "fas fa-eye";
    });
  });
}

/* ── Form Handlers ────────────────────────────── */
function initFormHandlers() {
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");

  if (loginForm) loginForm.addEventListener("submit", handleLogin);
  if (registerForm) registerForm.addEventListener("submit", handleRegister);
}

/* ── Login Handler ────────────────────────────── */
async function handleLogin(e) {
  e.preventDefault();

  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const formData = new FormData(form);

  // Validation
  if (!formData.get("email") || !formData.get("password")) {
    showAlert("Please fill in all fields", "error");
    return;
  }

  showLoading(submitBtn);

  try {
    const response = await fetch("backend/auth.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      showAlert("Login successful! Redirecting...", "success");
      setTimeout(() => (window.location.href = result.redirect), 800);
    } else {
      showAlert(result.message || "Login failed", "error");
    }
  } catch {
    showAlert("Network error. Please try again.", "error");
  } finally {
    hideLoading(submitBtn);
  }
}

/* ── Register Handler ─────────────────────────── */
async function handleRegister(e) {
  e.preventDefault();

  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const formData = new FormData(form);

  // Validation
  const { name, email, password, confirm_password } =
    Object.fromEntries(formData);

  if (!name || !email || !password || !confirm_password) {
    showAlert("Please fill in all fields", "error");
    return;
  }

  if (password !== confirm_password) {
    showAlert("Passwords do not match", "error");
    return;
  }

  if (password.length < 6) {
    showAlert("Password must be at least 6 characters", "error");
    return;
  }

  showLoading(submitBtn);

  try {
    const response = await fetch("backend/auth.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      showAlert("Registration successful! Redirecting to login...", "success");
      setTimeout(() => (window.location.href = "login.html"), 1500);
    } else {
      showAlert(result.message || "Registration failed", "error");
    }
  } catch {
    showAlert("Network error. Please try again.", "error");
  } finally {
    hideLoading(submitBtn);
  }
}
