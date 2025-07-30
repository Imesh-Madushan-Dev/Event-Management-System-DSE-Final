// Admin Dashboard JavaScript

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

  // Event form submission
  const eventForm = document.getElementById("event-form")
  if (eventForm) {
    eventForm.addEventListener("submit", handleEventSubmit)
  }
})

function openEventModal(eventId = null) {
  const modal = document.getElementById("event-modal")
  const form = document.getElementById("event-form")
  const title = document.getElementById("modal-title")
  const submitText = document.getElementById("submit-text")

  if (eventId) {
    // Edit mode
    title.textContent = "Edit Event"
    submitText.textContent = "Update Event"
    form.action.value = "update"
    document.getElementById("event_id").value = eventId

    // Load event data
    loadEventData(eventId)
  } else {
    // Create mode
    title.textContent = "Add New Event"
    submitText.textContent = "Create Event"
    form.action.value = "create"
    form.reset()
  }

  modal.classList.remove("hidden")
  modal.classList.add("flex")
}

function closeEventModal() {
  const modal = document.getElementById("event-modal")
  modal.classList.add("hidden")
  modal.classList.remove("flex")
}

async function loadEventData(eventId) {
  try {
    const response = await fetch(`backend/events.php?action=get&id=${eventId}`)
    const result = await response.json()

    if (result.success) {
      const event = result.event
      document.getElementById("event_name").value = event.name
      document.getElementById("event_description").value = event.description
      document.getElementById("event_img_url").value = event.img_url
      document.getElementById("event_price").value = event.price
      document.getElementById("event_branch").value = event.branch
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert("Failed to load event data")
  }
}

async function handleEventSubmit(e) {
  e.preventDefault()

  const form = e.target
  const submitBtn = form.querySelector('button[type="submit"]')
  const formData = new FormData(form)

  submitBtn.disabled = true
  submitBtn.textContent = "Loading..."

  try {
    const response = await fetch("backend/events.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      alert(result.message)
      closeEventModal()
      setTimeout(() => {
        location.reload()
      }, 1000)
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert("An error occurred")
  } finally {
    submitBtn.disabled = false
    submitBtn.textContent = "Submit"
  }
}

function editEvent(eventId) {
  openEventModal(eventId)
}

async function deleteEvent(eventId) {
  if (!confirm("Are you sure you want to delete this event?")) {
    return
  }

  try {
    const formData = new FormData()
    formData.append("action", "delete")
    formData.append("event_id", eventId)

    const response = await fetch("backend/events.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      alert(result.message)
      setTimeout(() => {
        location.reload()
      }, 1000)
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert("An error occurred")
  }
}

async function deleteUser(userId) {
  if (!confirm("Are you sure you want to delete this user?")) {
    return
  }

  try {
    const formData = new FormData()
    formData.append("action", "delete_user")
    formData.append("user_id", userId)

    const response = await fetch("backend/admin-actions.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      alert(result.message)
      setTimeout(() => {
        location.reload()
      }, 1000)
    } else {
      alert(result.message)
    }
  } catch (error) {
    alert("An error occurred")
  }
}

// Close modal when clicking outside
document.getElementById("event-modal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeEventModal()
  }
})

function showAlert(message, type) {
  alert(message)
}

function showLoading(button) {
  button.disabled = true
  button.textContent = "Loading..."
}

function hideLoading(button) {
  button.disabled = false
  button.textContent = "Submit"
}
