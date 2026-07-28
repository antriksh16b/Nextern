const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
const mobileClose = document.getElementById('mobileClose');

if (hamburger && mobileMenu && mobileClose) {
  hamburger.addEventListener('click', () => mobileMenu.classList.add('open'));
  mobileClose.addEventListener('click', () => mobileMenu.classList.remove('open'));
  document.querySelectorAll('.mobile-link').forEach((link) => {
    link.addEventListener('click', () => mobileMenu.classList.remove('open'));
  });
}

const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.1 }
);

document.querySelectorAll('.reveal').forEach((item) => observer.observe(item));

document.querySelectorAll('.faq-q').forEach((button) => {
  button.addEventListener('click', () => {
    const idx = button.getAttribute('data-faq');
    const answer = document.getElementById(`faq-${idx}`);
    const isOpen = answer?.classList.contains('open');

    document.querySelectorAll('.faq-a').forEach((panel) => panel.classList.remove('open'));
    document.querySelectorAll('.faq-q').forEach((faqBtn) => faqBtn.classList.remove('active'));

    if (!isOpen && answer) {
      answer.classList.add('open');
      button.classList.add('active');
    }
  });
});

const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  if (navbar) {
    navbar.style.padding = window.scrollY > 50 ? '.75rem 5%' : '1.2rem 5%';
  }
});

const meetingForm = document.getElementById('meetingForm');
const meetingStatus = document.getElementById('meetingStatus');
const meetingDateInput = document.getElementById('meetingDate');
const meetingAtInput = document.getElementById('meetingAt');
const slotGrid = document.getElementById('slotGrid');
const slotHelp = document.getElementById('slotHelp');

const BUSINESS_SLOTS = [
  '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
  '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
  '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'
];

function todayDateText() {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit'
  }).formatToParts(new Date());
  const value = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
  return `${value.year}-${value.month}-${value.day}`;
}

function clearSlotSelection() {
  meetingAtInput.value = '';
  slotGrid?.querySelectorAll('.slot-btn.selected').forEach((btn) => btn.classList.remove('selected'));
}

function isPastSlot(dateText, timeText) {
  if (dateText !== todayDateText()) {
    return false;
  }

  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Asia/Kolkata', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
  }).formatToParts(new Date());
  const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
  return timeText <= `${values.hour}:${values.minute}`;
}

function renderSlotButtons(occupiedSlots) {
  if (!slotGrid || !slotHelp || !meetingDateInput) {
    return;
  }

  const selectedDate = meetingDateInput.value;
  const occupiedSet = new Set(occupiedSlots);
  slotGrid.innerHTML = '';

  let availableCount = 0;
  BUSINESS_SLOTS.forEach((slot) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'slot-btn';
    button.textContent = slot;
    button.setAttribute('role', 'option');

    if (occupiedSet.has(slot) || isPastSlot(selectedDate, slot)) {
      button.disabled = true;
      button.classList.add('occupied');
      button.setAttribute('aria-disabled', 'true');
      slotGrid.appendChild(button);
      return;
    }

    button.addEventListener('click', () => {
      slotGrid.querySelectorAll('.slot-btn.selected').forEach((btn) => btn.classList.remove('selected'));
      button.classList.add('selected');
      meetingAtInput.value = `${selectedDate}T${slot}`;
      slotHelp.textContent = `Selected: ${selectedDate} ${slot}`;
    });

    availableCount += 1;
    slotGrid.appendChild(button);
  });

  if (availableCount === 0) {
    slotHelp.textContent = 'No slots are available on this date. Please choose another date.';
  } else {
    slotHelp.textContent = `${availableCount} slot(s) available. Occupied slots are disabled.`;
  }
}

async function loadAvailability() {
  if (!meetingDateInput || !slotHelp) {
    return;
  }

  const selectedDate = meetingDateInput.value;
  clearSlotSelection();

  if (!selectedDate) {
    slotHelp.textContent = 'Select a date to load available slots.';
    if (slotGrid) {
      slotGrid.innerHTML = '';
    }
    return;
  }

  slotHelp.textContent = 'Checking availability...';

  try {
    const response = await fetch(`/api.php?route=availability&date=${encodeURIComponent(selectedDate)}`);
    const data = await response.json();

    if (!response.ok) {
      slotHelp.textContent = data.message || 'Could not load slot availability.';
      return;
    }

    renderSlotButtons(Array.isArray(data.occupiedSlots) ? data.occupiedSlots : []);
  } catch (_error) {
    slotHelp.textContent = 'Could not load slots. Please check your connection.';
  }
}

if (meetingDateInput) {
  const today = todayDateText();
  meetingDateInput.min = today;
  meetingDateInput.value = today;
  meetingDateInput.addEventListener('change', loadAvailability);
  loadAvailability();
}

meetingForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (!meetingStatus || !meetingAtInput || !meetingDateInput) {
    return;
  }

  if (!meetingAtInput.value) {
    meetingStatus.textContent = 'Please choose an available time slot before submitting.';
    return;
  }

  meetingStatus.textContent = 'Scheduling your meeting...';

  const formData = new FormData(meetingForm);
  const payload = {
    fullName: String(formData.get('fullName') || '').trim(),
    email: String(formData.get('email') || '').trim(),
    phone: String(formData.get('phone') || '').trim(),
    businessName: String(formData.get('businessName') || '').trim(),
    meetingType: String(formData.get('meetingType') || 'Strategy Call').trim(),
    meetingAt: String(formData.get('meetingAt') || '').trim(),
    notes: String(formData.get('notes') || '').trim()
  };

  try {
    const response = await fetch('/api.php?route=bookings', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await response.json();
    if (!response.ok) {
      meetingStatus.textContent = data.message || 'Could not schedule meeting. Please try again.';
      if (response.status === 409) {
        await loadAvailability();
      }
      return;
    }

    meetingStatus.textContent = data.message || 'Meeting booked successfully.';
    meetingForm.reset();
    meetingDateInput.value = todayDateText();
    await loadAvailability();
  } catch (_error) {
    meetingStatus.textContent = 'Network error. Please try again in a moment.';
  }
});
