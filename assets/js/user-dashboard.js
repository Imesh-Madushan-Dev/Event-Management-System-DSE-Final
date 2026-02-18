/**
 * NIBM Events — User Dashboard JavaScript
 * Handles: tab switching, like/attend toggles, ticket purchase, QR codes
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
  initTabs();
});

/* ── Tab Switching ────────────────────────────── */
function initTabs() {
  const tabs = document.querySelectorAll(".tab-btn");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const targetId = tab.dataset.tab;

      // Update active tab styles
      tabs.forEach((t) => {
        t.classList.remove("active", "border-purple-500", "text-purple-600");
        t.classList.add("border-transparent", "text-gray-500");
      });
      tab.classList.add("active", "border-purple-500", "text-purple-600");
      tab.classList.remove("border-transparent", "text-gray-500");

      // Toggle content visibility
      contents.forEach((c) => c.classList.add("hidden"));
      const target = document.getElementById(`${targetId}-tab`);
      if (target) target.classList.remove("hidden");
    });
  });
}

/* ── Toggle Like ──────────────────────────────── */
async function toggleLike(eventId) {
  const btn = document.getElementById(`like-btn-${eventId}`);
  if (!btn || btn.disabled) return;

  const originalHTML = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>...';
  btn.disabled = true;

  try {
    const formData = new FormData();
    formData.append("action", "toggle_like");
    formData.append("event_id", eventId);

    const response = await fetch("backend/user-actions.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      // Update button appearance
      if (result.liked) {
        btn.className = btn.className.replace(
          /bg-gray-100 text-gray-600/g,
          "bg-red-100 text-red-600",
        );
        btn.innerHTML = '<i class="fas fa-heart mr-1"></i>Liked';
      } else {
        btn.className = btn.className.replace(
          /bg-red-100 text-red-600/g,
          "bg-gray-100 text-gray-600",
        );
        btn.innerHTML = '<i class="fas fa-heart mr-1"></i>Like';
      }

      // Update like count in parent card
      const card = btn.closest(".bg-white");
      const likeSpan = card?.querySelector(".fa-heart")?.parentElement;
      if (likeSpan)
        likeSpan.innerHTML = `<i class="fas fa-heart mr-1"></i>${result.count}`;
    } else {
      showAlert(result.message, "error");
      btn.innerHTML = originalHTML;
    }
  } catch {
    showAlert("An error occurred", "error");
    btn.innerHTML = originalHTML;
  } finally {
    btn.disabled = false;
  }
}

/* ── Toggle Attendance ────────────────────────── */
async function toggleAttendance(eventId) {
  const btn = document.getElementById(`attend-btn-${eventId}`);
  if (!btn || btn.disabled) return;

  const originalHTML = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>...';
  btn.disabled = true;

  try {
    const formData = new FormData();
    formData.append("action", "toggle_attendance");
    formData.append("event_id", eventId);

    const response = await fetch("backend/user-actions.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      if (result.attending) {
        btn.className = btn.className.replace(
          /bg-purple-100 text-purple-600/g,
          "bg-green-100 text-green-600",
        );
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Attending';
      } else {
        btn.className = btn.className.replace(
          /bg-green-100 text-green-600/g,
          "bg-purple-100 text-purple-600",
        );
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Attend';
      }

      const card = btn.closest(".bg-white");
      const attendSpan = card?.querySelector(".fa-users")?.parentElement;
      if (attendSpan)
        attendSpan.innerHTML = `<i class="fas fa-users mr-1"></i>${result.count}`;
    } else {
      showAlert(result.message, "error");
      btn.innerHTML = originalHTML;
    }
  } catch {
    showAlert("An error occurred", "error");
    btn.innerHTML = originalHTML;
  } finally {
    btn.disabled = false;
  }
}

/* ── Buy Ticket ───────────────────────────────── */
async function buyTicket(eventId, price) {
  try {
    const formData = new FormData();
    formData.append("action", "buy_ticket");
    formData.append("event_id", eventId);

    const response = await fetch("backend/user-actions.php", {
      method: "POST",
      body: formData,
    });
    const result = await response.json();

    if (result.success) {
      const params = new URLSearchParams({
        ticket_code: result.ticket_code,
        event_name: result.event_name,
        price: result.price,
        ticket_id: result.ticket_id,
      });
      window.location.href = `checkout.php?${params}`;
    } else {
      showAlert(result.message, "error");
    }
  } catch {
    showAlert("An error occurred", "error");
  }
}

/* ── QR Code Generation ──────────────────────── */
function generateQR(ticketCode, eventName) {
  const modal = document.getElementById("qr-modal");
  const container = document.getElementById("qr-code");
  const title = document.getElementById("qr-title");
  const text = document.getElementById("qr-code-text");

  if (!modal || !container) return;

  // Setup modal content
  title.textContent = eventName;
  text.textContent = `Ticket Code: ${ticketCode}`;
  container.innerHTML =
    '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i></div>';

  // Show modal
  modal.classList.remove("hidden");
  modal.classList.add("flex");

  // Generate QR via API
  const size = 200;
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(ticketCode)}&format=png&margin=10`;

  const img = new Image();
  img.src = qrUrl;
  img.alt = "QR Code";
  img.className = "mx-auto border-2 border-gray-200 rounded-xl";
  img.style.cssText = `width:${size}px;height:${size}px`;

  img.onload = () => {
    container.innerHTML = "";
    container.appendChild(img);
  };

  img.onerror = () => {
    container.innerHTML = `
      <div class="bg-gray-50 p-8 rounded-2xl text-center">
        <i class="fas fa-qrcode text-4xl text-gray-300 mb-3"></i>
        <p class="text-sm text-gray-600 mb-2">QR Code</p>
        <p class="font-mono text-xs bg-white p-2 rounded-xl border">${ticketCode}</p>
        <p class="text-xs text-gray-400 mt-2">Show this code at the event</p>
      </div>
    `;
  };
}

function closeQRModal() {
  const modal = document.getElementById("qr-modal");
  if (!modal) return;
  modal.classList.add("hidden");
  modal.classList.remove("flex");
}

// Close modal on backdrop click
document.getElementById("qr-modal")?.addEventListener("click", (e) => {
  if (e.target === e.currentTarget) closeQRModal();
});

/* ── Alert Toast (scoped for dashboard) ───────── */
function showAlert(message, type = "info") {
  const container = document.getElementById("alert-container");
  if (!container) return;

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
