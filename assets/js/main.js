/**
 * NIBM Events — Main JavaScript
 * Handles: navigation, mobile menu, smooth scroll, scroll animations
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
  initMobileMenu();
  initSmoothScroll();
  initNavScrollEffect();
  initScrollReveal();
});

/* ── Mobile Menu Toggle ────────────────────────── */
function initMobileMenu() {
  const btn = document.getElementById("mobile-menu-btn");
  const menu = document.getElementById("mobile-menu");
  if (!btn || !menu) return;

  btn.addEventListener("click", () => {
    const isOpen = !menu.classList.contains("hidden");
    menu.classList.toggle("hidden", isOpen);
    btn.querySelector("i").className = isOpen
      ? "fas fa-bars text-lg"
      : "fas fa-times text-lg";
  });

  // Close mobile menu when a link is clicked
  menu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      menu.classList.add("hidden");
      btn.querySelector("i").className = "fas fa-bars text-lg";
    });
  });
}

/* ── Smooth Scroll for anchor links ────────────── */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", (e) => {
      const targetId = anchor.getAttribute("href");
      if (targetId === "#") return;

      const target = document.querySelector(targetId);
      if (!target) return;

      e.preventDefault();
      target.scrollIntoView({ behavior: "smooth", block: "start" });

      // Update URL without jump
      history.pushState(null, "", targetId);
    });
  });
}

/* ── Nav Shadow on Scroll ──────────────────────── */
function initNavScrollEffect() {
  const nav =
    document.getElementById("main-nav") || document.querySelector("nav");
  if (!nav) return;

  const updateNav = () => {
    const scrolled = window.scrollY > 20;
    nav.classList.toggle("shadow-md", scrolled);
    nav.classList.toggle("border-transparent", scrolled);
  };

  window.addEventListener("scroll", updateNav, { passive: true });
  updateNav(); // run on load
}

/* ── Scroll-triggered Reveal Animations ────────── */
function initScrollReveal() {
  const reveals = document.querySelectorAll(
    ".reveal, .card-hover, section > div",
  );
  if (!reveals.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
        }
      });
    },
    { threshold: 0.08, rootMargin: "0px 0px -40px 0px" },
  );

  reveals.forEach((el) => observer.observe(el));
}

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

  // Auto-dismiss after 5 seconds
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
