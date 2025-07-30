// Main JavaScript for NIBM Events

document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuBtn = document.getElementById("mobile-menu-btn")
  const mobileMenu = document.getElementById("mobile-menu")

  if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener("click", () => {
      mobileMenu.classList.toggle("hidden")
    })
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Add scroll effect to navigation
  const nav = document.querySelector("nav")
  if (nav) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 50) {
        nav.classList.add("shadow-lg")
      } else {
        nav.classList.remove("shadow-lg")
      }
    })
  }

  // Animate elements on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("animate-slideUp")
      }
    })
  }, observerOptions)

  // Observe cards and sections
  document.querySelectorAll(".card-hover, section").forEach((el) => {
    observer.observe(el)
  })
})

// Utility functions
function showAlert(message, type = "info") {
  const alertContainer = document.getElementById("alert-container")
  if (!alertContainer) return

  const alert = document.createElement("div")
  alert.className = `alert alert-${type} animate-slideUp`
  alert.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `

  alertContainer.appendChild(alert)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alert.parentElement) {
      alert.remove()
    }
  }, 5000)
}

function showLoading(button) {
  button.classList.add("btn-loading")
  button.disabled = true
}

function hideLoading(button) {
  button.classList.remove("btn-loading")
  button.disabled = false
}
