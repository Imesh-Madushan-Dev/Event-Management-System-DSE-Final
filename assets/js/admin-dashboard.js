/**
 * NIBM Events — Admin Dashboard JavaScript
 * Handles: tab switching, event CRUD, user management, modals
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
  initTabs();
  initEventForm();
  initModalBackdrop();
});

/* ── Tab Switching ────────────────────────────── */
function initTabs() {
  const tabs = document.querySelectorAll(".tab-btn");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const targetId = tab.dataset.tab;

      tabs.forEach((t) => {
        t.classList.remove("active", "border-purple-500", "text-purple-600");
        t.classList.add("border-transparent", "text-gray-500");
      });
      tab.classList.add("active", "border-purple-500", "text-purple-600");
      tab.classList.remove("border-transparent", "text-gray-500");

      contents.forEach((c) => c.classList.add("hidden"));
      const target = document.getElementById(`${targetId}-tab`);
      if (target) target.classList.remove("hidden");
    });
  });
}

/* ── Event Form Submission ────────────────────── */
function initEventForm() {
  const form = document.getElementById("event-form");
  if (form) form.addEventListener("submit", handleEventSubmit);
}

/* ── Modal Backdrop Click ─────────────────────── */
function initModalBackdrop() {
  const modal = document.getElementById("event-modal");
  if (!modal) return;
  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeEventModal();
  });
}

/* ── Open Event Modal ─────────────────────────── */
function openEventModal(eventId = null) {
  const modal = document.getElementById("event-modal");
  const form = document.getElementById("event-form");
  const title = document.getElementById("modal-title");
  const submitText = document.getElementById("submit-text");
  const actionInput = form.querySelector('input[name="action"]');

  if (eventId) {
    title.textContent = "Edit Event";
    submitText.textContent = "Update Event";
    actionInput.value = "update";
    document.getElementById("event_id").value = eventId;
    loadEventData(eventId);
  } else {
    title.textContent = "Add New Event";
    submitText.textContent = "Create Event";
    actionInput.value = "create";
    form.reset();
  }

  modal.classList.remove("hidden");
  modal.classList.add("flex");
}

function closeEventModal() {
  const modal = document.getElementById("event-modal");
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

/* ── Load Event Data for Edit ─────────────────── */
async function loadEventData(eventId) {
  try {
    const response = await fetch(`backend/events.php?action=get&id=${eventId}`);
    const result = await response.json();

    if (result.success) {
      const event = result.event;
      document.getElementById("event_name").value = event.name;
      document.getElementById("event_description").value = event.description;
      document.getElementById("event_img_url").value = event.img_url;
      document.getElementById("event_price").value = event.price;
      document.getElementById("event_branch").value = event.branch;
    } else {
      showAlert(result.message, "error");
    }
  } catch {
    showAlert("Failed to load event data", "error");
  }
}

/* ── Handle Event Form Submit ─────────────────── */
async function handleEventSubmit(e) {
  e.preventDefault();

  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const formData = new FormData(form);

  showLoading(submitBtn);

  try {
    const response = await fetch("backend/events.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      showAlert(result.message, "success");
      closeEventModal();
      setTimeout(() => location.reload(), 800);
    } else {
      showAlert(result.message, "error");
    }
  } catch {
    showAlert("An error occurred", "error");
  } finally {
    hideLoading(submitBtn);
  }
}

/* ── Edit Event Shortcut ──────────────────────── */
function editEvent(eventId) {
  openEventModal(eventId);
}

/* ── Delete Event ─────────────────────────────── */
async function deleteEvent(eventId) {
  if (
    !confirm(
      "Are you sure you want to delete this event? This action cannot be undone.",
    )
  )
    return;

  try {
    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("event_id", eventId);

    const response = await fetch("backend/events.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 800);
    } else {
      showAlert(result.message, "error");
    }
  } catch {
    showAlert("An error occurred", "error");
  }
}

/* ── Delete User ──────────────────────────────── */
async function deleteUser(userId) {
  if (
    !confirm(
      "Are you sure you want to delete this user? This action cannot be undone.",
    )
  )
    return;

  try {
    const formData = new FormData();
    formData.append("action", "delete_user");
    formData.append("user_id", userId);

    const response = await fetch("backend/admin-actions.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      showAlert(result.message, "success");
      setTimeout(() => location.reload(), 800);
    } else {
      showAlert(result.message, "error");
    }
  } catch {
    showAlert("An error occurred", "error");
  }
}

/* ── Alert Toast ──────────────────────────────── */
function showAlert(message, type = "info") {
  const container = document.getElementById("alert-container");

  // Fallback to native alert if no container
  if (!container) {
    alert(message);
    return;
  }

  const icons = {
    success: "fas fa-check-circle",
    error: "fas fa-exclamation-circle",
    info: "fas fa-info-circle",
    warning: "fas fa-exclamation-triangle",
  };

  const el = document.createElement("div");
  el.className = `alert alert-${type} animate-slideUp mb-3`;
  el.innerHTML = `
    <div class="flex items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <i class="${icons[type] || icons.info}"></i>
        <span>${message}</span>
      </div>
      <button onclick="this.closest('.alert').remove()" class="text-current opacity-60 hover:opacity-100">
        <i class="fas fa-times text-xs"></i>
      </button>
    </div>
  `;

  container.appendChild(el);
  setTimeout(() => el.isConnected && el.remove(), 5000);
}

/* ── Button Loading State ─────────────────────── */
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
