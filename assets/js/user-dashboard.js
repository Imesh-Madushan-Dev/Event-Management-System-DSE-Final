// User Dashboard JavaScript

document.addEventListener("DOMContentLoaded", () => {
  // Tab switching
  const tabBtns = document.querySelectorAll(".tab-btn")
  const tabContents = document.querySelectorAll(".tab-content")

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const tabName = this.getAttribute("data-tab")

      // Update tab buttons
      tabBtns.forEach((b) => {
        b.classList.remove("active", "border-purple-500", "text-purple-600")
        b.classList.add("border-transparent", "text-gray-500")
      })

      this.classList.add("active", "border-purple-500", "text-purple-600")
      this.classList.remove("border-transparent", "text-gray-500")

      // Update tab contents
      tabContents.forEach((content) => {
        content.classList.add("hidden")
      })

      const targetContent = document.getElementById(tabName + "-tab")
      if (targetContent) {
        targetContent.classList.remove("hidden")
      }
    })
  })
})

async function toggleLike(eventId) {
  const btn = document.getElementById(`like-btn-${eventId}`)
  const originalText = btn.innerHTML

  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...'
  btn.disabled = true

  try {
    const formData = new FormData()
    formData.append("action", "toggle_like")
    formData.append("event_id", eventId)

    const response = await fetch("backend/user-actions.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      if (result.liked) {
        btn.classList.remove("bg-gray-100", "text-gray-600")
        btn.classList.add("bg-red-100", "text-red-600")
        btn.innerHTML = '<i class="fas fa-heart mr-1"></i>Liked'
      } else {
        btn.classList.remove("bg-red-100", "text-red-600")
        btn.classList.add("bg-gray-100", "text-gray-600")
        btn.innerHTML = '<i class="fas fa-heart mr-1"></i>Like'
      }

      // Update like count in the card
      const card = btn.closest(".bg-white")
      const likeCount = card.querySelector(".fa-heart").parentElement
      likeCount.innerHTML = `<i class="fas fa-heart mr-1"></i>${result.count}`
    } else {
      showAlert(result.message, "error")
      btn.innerHTML = originalText
    }
  } catch (error) {
    showAlert("An error occurred", "error")
    btn.innerHTML = originalText
  } finally {
    btn.disabled = false
  }
}

async function toggleAttendance(eventId) {
  const btn = document.getElementById(`attend-btn-${eventId}`)
  const originalText = btn.innerHTML

  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...'
  btn.disabled = true

  try {
    const formData = new FormData()
    formData.append("action", "toggle_attendance")
    formData.append("event_id", eventId)

    const response = await fetch("backend/user-actions.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      if (result.attending) {
        btn.classList.remove("bg-purple-100", "text-purple-600")
        btn.classList.add("bg-green-100", "text-green-600")
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Attending'
      } else {
        btn.classList.remove("bg-green-100", "text-green-600")
        btn.classList.add("bg-purple-100", "text-purple-600")
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Attend'
      }

      // Update attendance count in the card
      const card = btn.closest(".bg-white")
      const attendanceCount = card.querySelector(".fa-users").parentElement
      attendanceCount.innerHTML = `<i class="fas fa-users mr-1"></i>${result.count}`
    } else {
      showAlert(result.message, "error")
      btn.innerHTML = originalText
    }
  } catch (error) {
    showAlert("An error occurred", "error")
    btn.innerHTML = originalText
  } finally {
    btn.disabled = false
  }
}

async function buyTicket(eventId, price) {
  try {
    const formData = new FormData()
    formData.append("action", "buy_ticket")
    formData.append("event_id", eventId)

    const response = await fetch("backend/user-actions.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      // Always redirect to checkout page for both free and paid events
      const checkoutUrl = `checkout.php?ticket_code=${result.ticket_code}&event_name=${encodeURIComponent(result.event_name)}&price=${result.price}&ticket_id=${result.ticket_id}`
      window.location.href = checkoutUrl
    } else {
      showAlert(result.message, "error")
    }
  } catch (error) {
    showAlert("An error occurred", "error")
  }
}

// Alternative QR Code generation using QR Server API
function generateQR(ticketCode, eventName) {
  const modal = document.getElementById("qr-modal")
  const qrContainer = document.getElementById("qr-code")
  const qrTitle = document.getElementById("qr-title")
  const qrText = document.getElementById("qr-code-text")

  // Clear previous QR code
  qrContainer.innerHTML = ""

  // Set title and text
  qrTitle.textContent = eventName
  qrText.textContent = `Ticket Code: ${ticketCode}`

  // Show loading
  qrContainer.innerHTML =
    '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i></div>'

  // Show modal first
  modal.classList.remove("hidden")
  modal.classList.add("flex")

  // Generate QR code using QR Server API
  const qrSize = 200
  const qrData = encodeURIComponent(ticketCode)
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${qrSize}x${qrSize}&data=${qrData}&format=png&margin=10`

  // Create image element
  const qrImage = document.createElement("img")
  qrImage.src = qrUrl
  qrImage.alt = "QR Code"
  qrImage.className = "mx-auto border-2 border-gray-200 rounded-lg"
  qrImage.style.width = `${qrSize}px`
  qrImage.style.height = `${qrSize}px`

  qrImage.onload = () => {
    qrContainer.innerHTML = ""
    qrContainer.appendChild(qrImage)
  }

  qrImage.onerror = () => {
    // Fallback to text-based QR if image fails
    qrContainer.innerHTML = `
      <div class="bg-gray-100 p-8 rounded-lg text-center">
        <i class="fas fa-qrcode text-4xl text-gray-400 mb-4"></i>
        <p class="text-sm text-gray-600 mb-2">QR Code</p>
        <p class="font-mono text-xs bg-white p-2 rounded border">${ticketCode}</p>
        <p class="text-xs text-gray-500 mt-2">Show this code at the event</p>
      </div>
    `
  }
}

function closeQRModal() {
  const modal = document.getElementById("qr-modal")
  modal.classList.add("hidden")
  modal.classList.remove("flex")
}

// Close modal when clicking outside
document.getElementById("qr-modal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeQRModal()
  }
})

// Declare showAlert function
function showAlert(message, type) {
  const alertContainer = document.getElementById("alert-container")
  const alertElement = document.createElement("div")
  alertElement.classList.add("alert", `alert-${type}`)
  alertElement.textContent = message
  alertContainer.appendChild(alertElement)

  setTimeout(() => {
    alertContainer.removeChild(alertElement)
  }, 3000)
}
