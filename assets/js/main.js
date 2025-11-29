	// Main JS for EventSphere
	console.log('EventSphere main loaded');

	// MOBILE MENU TOGGLE
	document.addEventListener('DOMContentLoaded', function () {
		const btn = document.getElementById('mobileMenuButton');
		const menu = document.getElementById('mobileMenu');
		const openIcon = document.getElementById('hamburgerOpen');
		const closeIcon = document.getElementById('hamburgerClose');
		const sidebarBackdrop = document.getElementById('sidebarBackdrop');
	if (btn && menu) {
		btn.addEventListener('click', function () {
			menu.classList.toggle('hidden');
			openIcon.classList.toggle('hidden');
			closeIcon.classList.toggle('hidden');
			const expanded = btn.getAttribute('aria-expanded') === 'true';
			btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
		});
	}

	// EXIT-INTENT MODAL: show once per session
	const modal = document.getElementById('exitIntentModal');
	const modalClose = document.getElementById('exitIntentClose');
	let exitShown = sessionStorage.getItem('exitIntentShown');
	function showModal() {
		if (!modal || exitShown) return;
		modal.classList.remove('hidden');
		modal.setAttribute('aria-hidden', 'false');
		sessionStorage.setItem('exitIntentShown', '1');
		exitShown = '1';
	}

	if (typeof window !== 'undefined') {
		let mouseY = 0;
		window.addEventListener('mousemove', function (e) {
			mouseY = e.clientY;
		});
		window.addEventListener('mouseout', function (e) {
			// Only trigger when leaving at the top on desktop
			if (e.clientY <= 0 && !exitShown) {
				showModal();
			}
		});
	}

	if (modalClose) {
		modalClose.addEventListener('click', function () {
			if (modal) {
				modal.classList.add('hidden');
				modal.setAttribute('aria-hidden', 'true');
			}
		});
	}

	// SIDEBAR TOGGLE (for dashboard & admin)
	const sidebarToggleBtn = document.getElementById('sidebarToggle');
	const sidebarEl = document.getElementById('sidebar');
	const mobileSidebarEl = document.getElementById('mobileSidebar');
	if (sidebarToggleBtn && (sidebarEl || mobileSidebarEl)) {
		sidebarToggleBtn.addEventListener('click', function () {
			if (sidebarEl) sidebarEl.classList.toggle('-translate-x-full');
			if (mobileSidebarEl) mobileSidebarEl.classList.toggle('-translate-x-full');
			// update aria-expanded attribute
			const expanded = sidebarToggleBtn.getAttribute('aria-expanded') === 'true';
			sidebarToggleBtn.setAttribute('aria-expanded', (!expanded).toString());
			if (sidebarBackdrop) sidebarBackdrop.classList.toggle('hidden');
		});
	}

	// Allow clicking overlay to close the sidebar on mobile
	if (sidebarBackdrop && (sidebarEl || mobileSidebarEl) && sidebarToggleBtn) {
		sidebarBackdrop.addEventListener('click', function () {
			if (sidebarEl) sidebarEl.classList.add('-translate-x-full');
			if (mobileSidebarEl) mobileSidebarEl.classList.add('-translate-x-full');
			sidebarBackdrop.classList.add('hidden');
			sidebarToggleBtn.setAttribute('aria-expanded', 'false');
		});
	}

	// ADMIN: Tab switching and Add Event modal behavior
	window.switchAdminTab = function(tabId) {
		const tabs = document.querySelectorAll('.admin-tab');
		tabs.forEach(t => t.classList.add('hidden'));
		const sel = document.getElementById(tabId + 'Tab');
		if (sel) sel.classList.remove('hidden');
		// Update active state for sidebar buttons
		const navBtns = document.querySelectorAll('.admin-nav-btn');
		navBtns.forEach(b => {
			// clear active classes
			b.classList.remove('bg-slate-800');
			b.classList.remove('text-white');
		});
		const activeBtn = document.querySelector('.admin-nav-btn[data-tab="' + tabId + '"]');
		if (activeBtn) {
			activeBtn.classList.add('bg-slate-800');
			activeBtn.classList.add('text-white');
		}
	};

	window.openAddEventModal = function() {
		const modal = document.getElementById('addEventModal');
		if (!modal) return;
		// clear any existing values
		const idField = document.getElementById('event_id_hidden');
		if (idField) idField.value = '';
		const fTitle = document.getElementById('event_title'); if (fTitle) fTitle.value = '';
		const fDate = document.getElementById('event_date'); if (fDate) fDate.value = '';
		const fVenue = document.getElementById('event_venue'); if (fVenue) fVenue.value = '';
		const fDescription = document.getElementById('event_description'); if (fDescription) fDescription.value = '';
		const fStatus = document.getElementById('event_status'); if (fStatus) fStatus.value = 'Scheduled';
		// set title
		const titleEl = modal.querySelector('.text-lg.font-semibold');
		if (titleEl) titleEl.textContent = 'Create Event';
		// reset dynamic sections
		const sch = document.getElementById('scheduleContainer');
		if (sch) { sch.innerHTML = ''; sch.dataset.nextIndex = '0'; }
		const sp = document.getElementById('speakersContainer');
		if (sp) { sp.innerHTML = ''; sp.dataset.nextIndex = '0'; }
		// Add an initial blank row for convenience
		addScheduleRow();
		addSpeakerRow();
		modal.classList.remove('hidden');
		modal.setAttribute('aria-hidden','false');
	};
	window.closeAddEventModal = function() {
		const modal = document.getElementById('addEventModal');
		if (modal) { modal.classList.add('hidden'); modal.setAttribute('aria-hidden','true'); }
	};
	window.openEditEventModal = function(eventId) {
		const modal = document.getElementById('addEventModal');
		if (!modal) return;
		// find row for id
		const row = document.querySelector('tr[data-id="' + eventId + '"]');
		if (!row) return modal.classList.remove('hidden');
		const idField = document.getElementById('event_id_hidden'); if (idField) idField.value = eventId;
		const fTitle = document.getElementById('event_title'); if (fTitle) fTitle.value = row.dataset.title || '';
		const fDate = document.getElementById('event_date'); if (fDate) {
			const dateStr = row.dataset.date || '';
			if (dateStr) {
				const d = new Date(dateStr);
				if (!isNaN(d)) {
					fDate.value = d.toISOString().substring(0,16);
				} else {
					fDate.value = dateStr;
				}
			} else {
				fDate.value = '';
			}
		}
		const fVenue = document.getElementById('event_venue'); if (fVenue) fVenue.value = row.dataset.venue || '';
		const fDescription = document.getElementById('event_description'); if (fDescription) fDescription.value = row.dataset.description || '';
		const fStatus = document.getElementById('event_status'); if (fStatus) fStatus.value = row.dataset.status || 'Scheduled';
		const fCapacity = document.getElementById('event_capacity'); if (fCapacity) fCapacity.value = row.dataset.capacity || '';
		const titleEl = modal.querySelector('.text-lg.font-semibold');
		if (titleEl) titleEl.textContent = 'Edit Event';
		modal.classList.remove('hidden');
		modal.setAttribute('aria-hidden','false');
		// fetch JSON for schedules & speakers
		fetch('/eventsphere/get_event_json.php?id=' + encodeURIComponent(eventId)).then(r => {
			if (!r.ok) return null;
			return r.json();
		}).then(data => {
			if (!data) return;
			// Clear existing rows
			const sch = document.getElementById('scheduleContainer'); if (sch) { sch.innerHTML = ''; sch.dataset.nextIndex = '0'; }
			const sp = document.getElementById('speakersContainer'); if (sp) { sp.innerHTML = ''; sp.dataset.nextIndex = '0'; }
				if (data.sessions && Array.isArray(data.sessions)) {
				data.sessions.forEach(s => addScheduleRow(s));
			}
			if (data.speakers && Array.isArray(data.speakers)) {
				data.speakers.forEach(spk => addSpeakerRow({ name: spk.name, role: spk.role }));
			}
			// update other fields from JSON if present
			if (typeof data.title !== 'undefined' && fTitle) fTitle.value = data.title;
			if (typeof data.date !== 'undefined' && fDate) fDate.value = (new Date(data.date)).toISOString().substring(0,16);
			if (typeof data.venue !== 'undefined' && fVenue) fVenue.value = data.venue;
			if (typeof data.description !== 'undefined' && fDescription) fDescription.value = data.description;
			if (typeof data.status !== 'undefined' && fStatus) fStatus.value = data.status;
				if (typeof data.capacity !== 'undefined' && fCapacity) fCapacity.value = data.capacity;
		}).catch(err => {
			console.warn('Failed to load event JSON', err);
		});
	};

	// --- Dynamic schedule + speaker row management ---
	window.addScheduleRow = function(init) {
		init = init || {};
		const container = document.getElementById('scheduleContainer');
		if (!container) return;
		let index = parseInt(container.dataset.nextIndex || '0', 10);
		const row = document.createElement('div');
		row.className = 'schedule-row flex gap-2 items-center';
		const inputTime = document.createElement('input');
		inputTime.type = 'text'; inputTime.placeholder = 'e.g. 09:00 AM'; inputTime.className = 'border px-2 py-1 rounded w-28';
		inputTime.name = `schedule[${index}][time]`;
		inputTime.value = init.time || '';
		const inputTitle = document.createElement('input');
		inputTitle.type = 'text'; inputTitle.placeholder = 'Session title'; inputTitle.className = 'border px-2 py-1 rounded flex-1';
		inputTitle.name = `schedule[${index}][title]`;
		inputTitle.value = init.title || '';
		const inputSpeaker = document.createElement('input');
		inputSpeaker.type = 'text'; inputSpeaker.placeholder = 'Speaker'; inputSpeaker.className = 'border px-2 py-1 rounded w-40';
		inputSpeaker.name = `schedule[${index}][speaker]`;
		inputSpeaker.value = init.speaker || '';
		const btn = document.createElement('button');
		btn.type = 'button'; btn.className = 'px-2 py-1 text-red-500'; btn.textContent = 'Remove';
		btn.addEventListener('click', function() { row.remove(); });
		row.appendChild(inputTime);
		row.appendChild(inputTitle);
		row.appendChild(inputSpeaker);
		row.appendChild(btn);
		container.appendChild(row);
		container.dataset.nextIndex = String(index + 1);
	};

	window.addSpeakerRow = function(init) {
		init = init || {};
		const container = document.getElementById('speakersContainer');
		if (!container) return;
		let index = parseInt(container.dataset.nextIndex || '0', 10);
		const row = document.createElement('div');
		row.className = 'speaker-row flex gap-2 items-center';
		const inputName = document.createElement('input');
		inputName.type = 'text'; inputName.placeholder = 'Name'; inputName.className = 'border px-2 py-1 rounded w-48';
		inputName.name = `speakers[${index}][name]`;
		inputName.value = init.name || '';
		const inputRole = document.createElement('input');
		inputRole.type = 'text'; inputRole.placeholder = 'Role'; inputRole.className = 'border px-2 py-1 rounded flex-1';
		inputRole.name = `speakers[${index}][role]`;
		inputRole.value = init.role || '';
		const btn = document.createElement('button');
		btn.type = 'button'; btn.className = 'px-2 py-1 text-red-500'; btn.textContent = 'Remove';
		btn.addEventListener('click', function() { row.remove(); });
		row.appendChild(inputName);
		row.appendChild(inputRole);
		row.appendChild(btn);
		container.appendChild(row);
		container.dataset.nextIndex = String(index + 1);
	};

	window.clearScheduleRows = function() { const container = document.getElementById('scheduleContainer'); if (container) { container.innerHTML = ''; container.dataset.nextIndex = '0'; } };
	window.clearSpeakerRows = function() { const container = document.getElementById('speakersContainer'); if (container) { container.innerHTML = ''; container.dataset.nextIndex = '0'; } };

	// Close modal on Escape key
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
			modal.classList.add('hidden');
			modal.setAttribute('aria-hidden', 'true');
		}
	});

	// Close modal by clicking outside the content
	if (modal) {
		modal.addEventListener('click', function (e) {
			if (e.target === modal) {
				modal.classList.add('hidden');
				modal.setAttribute('aria-hidden', 'true');
			}
		});
	}
});
// Ensure lucide icons are created if the library has been loaded and there are uninitialised icon tags
if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
	lucide.createIcons();
}

// -- Time picker helper for schedule time inputs (text based) --
function createTimePickerMenu() {
	const menu = document.createElement('div');
	menu.className = 'timepicker-menu absolute z-50 bg-white border rounded shadow p-2 max-h-48 overflow-auto text-sm';
	menu.style.display = 'none';
	document.body.appendChild(menu);
	return menu;
}

const timePickerMenu = createTimePickerMenu();
// generate times in 15-minute increments
function generateTimes() {
	const times = [];
	for (let h = 0; h < 24; h++) {
		for (let m = 0; m < 60; m += 15) {
			const date = new Date(0,0,0,h,m);
			const hr = date.getHours();
			const min = date.getMinutes();
			const ampm = hr >= 12 ? 'PM' : 'AM';
			const hr12 = ((hr + 11) % 12 + 1);
			times.push((hr12 < 10 ? '0' + hr12 : hr12) + ':' + (min < 10 ? '0' + min : min) + ' ' + ampm);
		}
	}
	return times;
}
const timeOptions = generateTimes();

function showTimePickerForInput(input) {
	if (!input) return;
	const rect = input.getBoundingClientRect();
	timePickerMenu.innerHTML = '';
	timeOptions.forEach(t => {
		const div = document.createElement('div');
		div.className = 'px-2 py-1 hover:bg-slate-100 cursor-pointer';
		div.textContent = t;
		div.addEventListener('mousedown', function (ev) {
			ev.preventDefault(); // keep focus on input
			input.value = t;
			hideTimePicker();
			input.dispatchEvent(new Event('change'));
		});
		timePickerMenu.appendChild(div);
	});
	timePickerMenu.style.display = 'block';
	timePickerMenu.style.left = (rect.left + window.scrollX) + 'px';
	timePickerMenu.style.top = (rect.bottom + window.scrollY + 6) + 'px';
}

function hideTimePicker() {
	if (timePickerMenu) timePickerMenu.style.display = 'none';
}

// Normalize time strings (supports 24-hour and 12-hour, returns `HH:MM AM/PM`)
function normalizeTimeString(v) {
	if (!v || typeof v !== 'string') return '';
	const s = v.trim();
	// match 24-hour (HH:MM)
	const m24 = s.match(/^([01]?\d|2[0-3]):([0-5][0-9])$/);
	if (m24) {
		let h = parseInt(m24[1], 10); const mm = m24[2];
		const ampm = h >= 12 ? 'PM' : 'AM';
		if (h === 0) h = 12; if (h > 12) h = h - 12;
		return (h < 10 ? '0' + h : '' + h) + ':' + mm + ' ' + ampm;
	}
	// match 12-hour with am/pm
	const m12 = s.match(/^([1-9]|1[0-2]):([0-5][0-9])\s*([AaPp][Mm])$/);
	if (m12) {
		let hh = parseInt(m12[1], 10); const mm = m12[2]; const ap = m12[3].toUpperCase();
		return (hh < 10 ? '0' + hh : '' + hh) + ':' + mm + ' ' + ap;
	}
	// fallback: try to parse 'h:mmam' with no space
	const m12b = s.match(/^([1-9]|1[0-2]):([0-5][0-9])([AaPp][Mm])$/);
	if (m12b) {
		let hh = parseInt(m12b[1], 10); const mm = m12b[2]; const ap = m12b[3].toUpperCase();
		return (hh < 10 ? '0' + hh : '' + hh) + ':' + mm + ' ' + ap;
	}
	return s; // leave as-is if no match
}

// Delegate normalization on change/blur for schedule time inputs
document.addEventListener('change', function (e) {
	const el = e.target;
	if (el && el.tagName === 'INPUT' && el.name && el.name.match(/^schedule\[\d+\]\[time\]$/)) {
		el.value = normalizeTimeString(el.value);
	}
});

// Attach delegated focusin/out listeners so dynamically added inputs are covered
document.addEventListener('focusin', function (e) {
	const el = e.target;
	if (!el) return;
	// match name pattern schedule[*][time]
	if (el.tagName === 'INPUT' && el.name && el.name.match(/^schedule\[\d+\]\[time\]$/)) {
		showTimePickerForInput(el);
	}
});
document.addEventListener('mousedown', function (e) {
	// click outside time picker should hide it
	if (!timePickerMenu) return;
	const tg = e.target;
	if (timePickerMenu.contains(tg)) return;
	// also ignore if clicking an input for schedule time
	if (tg && tg.tagName === 'INPUT' && tg.name && tg.name.match(/^schedule\[\d+\]\[time\]$/)) return;
	hideTimePicker();
});
