const adminLoginCard = document.getElementById('adminLoginCard');
const adminDashboard = document.getElementById('adminDashboard');
const ownerLoginForm = document.getElementById('ownerLoginForm');
const ownerLoginStatus = document.getElementById('ownerLoginStatus');
const ownerLogoutBtn = document.getElementById('ownerLogoutBtn');
const bookingRows = document.getElementById('bookingRows');
const adminDataStatus = document.getElementById('adminDataStatus');
const adminSettingsForm = document.getElementById('adminSettingsForm');
const adminEmailList = document.getElementById('adminEmailList');
const adminSettingsStatus = document.getElementById('adminSettingsStatus');

// Calendar elements
const calendarMonthYear = document.getElementById('calendarMonthYear');
const calendarDays = document.getElementById('calendarDays');
const prevMonthBtn = document.getElementById('prevMonthBtn');
const nextMonthBtn = document.getElementById('nextMonthBtn');
const dayDetailsPanel = document.getElementById('dayDetailsPanel');
const dayDetailsTitle = document.getElementById('dayDetailsTitle');
const dayDetailsContent = document.getElementById('dayDetailsContent');
const closeDayDetailsBtn = document.getElementById('closeDayDetails');

let allBookings = [];
let currentCalendarDate = new Date();
const BUSINESS_TIME_ZONE = 'Asia/Kolkata';

function formatDate(isoDate) {
  const date = new Date(isoDate);
  if (Number.isNaN(date.getTime())) {
    return '-';
  }
  return date.toLocaleString('en-IN', { timeZone: BUSINESS_TIME_ZONE });
}

function formatDateDMY(date) {
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatTime(isoDate) {
  const date = new Date(isoDate);
  if (Number.isNaN(date.getTime())) {
    return '-';
  }
  return date.toLocaleTimeString('en-IN', { timeZone: BUSINESS_TIME_ZONE, hour: '2-digit', minute: '2-digit', hour12: true });
}

function setAuthView(authenticated) {
  if (authenticated) {
    adminLoginCard.classList.add('admin-hidden');
    adminDashboard.classList.remove('admin-hidden');
  } else {
    adminDashboard.classList.add('admin-hidden');
    adminLoginCard.classList.remove('admin-hidden');
  }
}

async function fetchSettings() {
  if (!adminSettingsForm || !adminEmailList) {
    return;
  }

  try {
    const response = await fetch('/api.php?route=admin/settings');
    if (!response.ok) {
      throw new Error('Could not load notification settings.');
    }

    const data = await response.json();
    adminEmailList.value = Array.isArray(data.adminEmails) ? data.adminEmails.join(',\n') : '';
  } catch (error) {
    adminSettingsStatus.textContent = error.message;
  }
}

async function fetchBookings() {
  adminDataStatus.textContent = 'Loading bookings...';

  const response = await fetch('/api.php?route=admin/bookings');
  if (!response.ok) {
    throw new Error('Could not load bookings.');
  }

  const data = await response.json();
  allBookings = Array.isArray(data.bookings) ? data.bookings : [];
  
  bookingRows.innerHTML = '';

  if (!allBookings.length) {
    bookingRows.innerHTML = '<tr><td colspan="7">No meetings scheduled yet.</td></tr>';
  } else {
    allBookings.forEach((booking) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${booking.fullName || '-'}</td>
        <td>${booking.email || '-'}</td>
        <td>${booking.phone || '-'}</td>
        <td>${booking.businessName || '-'}</td>
        <td>${formatDate(booking.meetingAt)}</td>
        <td>${booking.meetingType || '-'}</td>
        <td>${booking.notes || '-'}</td>
      `;
      bookingRows.appendChild(row);
    });
  }

  adminDataStatus.textContent = `Total bookings: ${data.total}`;
  renderCalendar();
}

function getBookingsForDate(dateString) {
  return allBookings.filter((booking) => {
    const bookingDate = new Date(booking.meetingAt);
    if (Number.isNaN(bookingDate.getTime())) {
      return false;
    }
    const parts = new Intl.DateTimeFormat('en-CA', {
      timeZone: BUSINESS_TIME_ZONE, year: 'numeric', month: '2-digit', day: '2-digit'
    }).formatToParts(bookingDate);
    const value = Object.fromEntries(parts.map(({ type, value }) => [type, value]));
    return `${value.year}-${value.month}-${value.day}` === dateString;
  });
}

function renderCalendar() {
  if (!calendarMonthYear || !calendarDays) {
    return;
  }

  const year = currentCalendarDate.getFullYear();
  const month = currentCalendarDate.getMonth();

  calendarMonthYear.textContent = new Date(year, month).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long'
  });

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const daysInPrevMonth = new Date(year, month, 0).getDate();

  calendarDays.innerHTML = '';

  for (let i = firstDay - 1; i >= 0; i--) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day other-month';
    dayDiv.textContent = daysInPrevMonth - i;
    calendarDays.appendChild(dayDiv);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day';

    const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const dayBookings = getBookingsForDate(dateString);

    dayDiv.textContent = day;

    if (dayBookings.length > 0) {
      dayDiv.classList.add('booked');
      dayDiv.setAttribute('data-date', dateString);
      dayDiv.addEventListener('click', () => showDayDetails(dateString, dayBookings));
    }

    calendarDays.appendChild(dayDiv);
  }

  const remainingDays = 42 - (firstDay + daysInMonth);
  for (let day = 1; day <= remainingDays; day++) {
    const dayDiv = document.createElement('div');
    dayDiv.className = 'calendar-day other-month';
    dayDiv.textContent = day;
    calendarDays.appendChild(dayDiv);
  }
}

function showDayDetails(dateString, dayBookings) {
  if (!dayDetailsPanel || !dayDetailsTitle || !dayDetailsContent) {
    return;
  }

  dayDetailsTitle.textContent = formatDateDMY(new Date(dateString + 'T00:00'));
  dayDetailsContent.innerHTML = '';

  dayBookings.forEach((booking) => {
    const bookingDiv = document.createElement('div');
    bookingDiv.className = 'day-booking';
    
    const timeDiv = document.createElement('div');
    timeDiv.className = 'day-booking-time';
    timeDiv.textContent = formatTime(booking.meetingAt);
    
    const nameDiv = document.createElement('div');
    nameDiv.textContent = `${booking.fullName} (${booking.businessName || 'N/A'})`;
    
    const typeDiv = document.createElement('div');
    typeDiv.textContent = `Type: ${booking.meetingType}`;
    
    const emailDiv = document.createElement('div');
    emailDiv.textContent = `Email: ${booking.email}`;
    emailDiv.style.fontSize = '0.85em';
    emailDiv.style.color = '#666';
    
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'day-booking-actions';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'action-btn cancel-btn';
    cancelBtn.textContent = 'Cancel Meeting';
    cancelBtn.addEventListener('click', () => cancelBooking(booking.id));
    
    const editBtn = document.createElement('button');
    editBtn.className = 'action-btn edit-btn';
    editBtn.textContent = 'Edit';
    editBtn.addEventListener('click', () => editBooking(booking));
    
    actionsDiv.appendChild(editBtn);
    actionsDiv.appendChild(cancelBtn);
    
    bookingDiv.appendChild(timeDiv);
    bookingDiv.appendChild(nameDiv);
    bookingDiv.appendChild(typeDiv);
    bookingDiv.appendChild(emailDiv);
    bookingDiv.appendChild(actionsDiv);
    dayDetailsContent.appendChild(bookingDiv);
  });

  dayDetailsPanel.classList.remove('admin-hidden');
}

async function cancelBooking(bookingId) {
  if (!confirm('Are you sure you want to cancel this meeting?')) {
    return;
  }

  try {
    const response = await fetch(`/api.php?route=admin/bookings&id=${bookingId}`, {
      method: 'DELETE'
    });

    if (!response.ok) {
      const data = await response.json();
      alert(data.message || 'Failed to cancel booking.');
      return;
    }

    alert('Meeting cancelled successfully.');
    hideDayDetails();
    await fetchBookings();
  } catch (error) {
    alert('Error cancelling booking: ' + error.message);
  }
}

function editBooking(booking) {
  const newNotes = prompt('Edit notes:', booking.notes || '');
  if (newNotes === null) {
    return;
  }

  updateBooking(booking.id, { notes: newNotes });
}

async function updateBooking(bookingId, updates) {
  try {
    const response = await fetch(`/api.php?route=admin/bookings&id=${bookingId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updates)
    });

    if (!response.ok) {
      const data = await response.json();
      alert(data.message || 'Failed to update booking.');
      return;
    }

    alert('Booking updated successfully.');
    hideDayDetails();
    await fetchBookings();
  } catch (error) {
    alert('Error updating booking: ' + error.message);
  }
}

function hideDayDetails() {
  if (dayDetailsPanel) {
    dayDetailsPanel.classList.add('admin-hidden');
  }
}

async function checkSession() {
  try {
    const response = await fetch('/api.php?route=admin/session');
    const data = await response.json();
    setAuthView(Boolean(data.authenticated));
    if (data.authenticated) {
      await fetchBookings();
    }
  } catch (_error) {
    setAuthView(false);
  }
}

ownerLoginForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  ownerLoginStatus.textContent = 'Checking credentials...';

  const formData = new FormData(ownerLoginForm);
  const payload = {
    username: String(formData.get('username') || '').trim(),
    password: String(formData.get('password') || '')
  };

  try {
    const response = await fetch('/api.php?route=admin/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await response.json();
    if (!response.ok) {
      ownerLoginStatus.textContent = data.message || 'Login failed.';
      return;
    }

    ownerLoginStatus.textContent = data.message;
    setAuthView(true);
    await Promise.all([fetchBookings(), fetchSettings()]);
    ownerLoginForm.reset();
  } catch (_error) {
    ownerLoginStatus.textContent = 'Something went wrong. Please try again.';
  }
});

ownerLogoutBtn?.addEventListener('click', async () => {
  await fetch('/api.php?route=admin/logout', { method: 'POST' });
  setAuthView(false);
  ownerLoginStatus.textContent = 'Logged out successfully.';
});

adminSettingsForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (!adminEmailList) {
    return;
  }

  adminSettingsStatus.textContent = 'Saving recipients...';

  try {
    const response = await fetch('/api.php?route=admin/settings', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ adminEmails: adminEmailList.value })
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || 'Could not save recipients.');
    }

    adminSettingsStatus.textContent = data.message || 'Recipients updated.';
  } catch (error) {
    adminSettingsStatus.textContent = error.message;
  }
});

prevMonthBtn?.addEventListener('click', () => {
  currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
  renderCalendar();
});

nextMonthBtn?.addEventListener('click', () => {
  currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
  renderCalendar();
});

closeDayDetailsBtn?.addEventListener('click', hideDayDetails);

checkSession();

