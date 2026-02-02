 // Self-diagnose form logic
    $("#selfDiagForm").on("submit", function (e) {
      e.preventDefault();
      let total = 0;
      $(".diag-checkbox:checked").each(function () {
        total += Number($(this).val() || 0);
      });

      let message = "";
      let colorClass = "";

      if (total <= 4) {
        message = `Score: ${total} — Low / No gambling disorder indicated.`;
        colorClass = "text-success";
      } else if (total <= 7) {
        message = `Score: ${total} — Moderate risk; consider seeking support.`;
        colorClass = "text-warning";
      } else {
        message = `Score: ${total} — High risk for gambling disorder; please seek professional help.`;
        colorClass = "text-danger";
      }

      $("#diagMessage").removeClass("text-success text-warning text-danger").addClass(colorClass).text(message);
      $("#diagResult").show();
      // Optionally scroll result into view inside modal
      $("#selfDiagModal .modal-body").animate({ scrollTop: $("#diagResult").offset().top }, 300);
    });

    // Reset modal when closed
    $('#selfDiagModal').on('hidden.bs.modal', function () {
      $("#selfDiagForm")[0].reset();
      $("#diagResult").hide();
      $("#diagMessage").text("").removeClass("text-success text-warning text-danger");
    });

    



    
 // Conduct Interview Page - only run if on conduct.html
 (function initConduct() {
   const assessmentForm = document.getElementById('assessmentForm');
   if (!assessmentForm) return; // Exit if not on conduct page

   // Load meeting details from sessionStorage
   const payloadRaw = sessionStorage.getItem('conduct_payload');
   const payload = payloadRaw ? JSON.parse(payloadRaw) : null;
   const meetingTitle = document.getElementById('meetingTitle');
   const meetingMeta = document.getElementById('meetingMeta');
   if (payload && meetingTitle && meetingMeta) {
     meetingTitle.textContent = payload.title || 'Interview';
     meetingMeta.textContent = `${payload.room || ''} • ${payload.date || ''} • ${payload.time || ''}`;
     const candidateName = document.getElementById('candidateName');
     if (candidateName) {
       candidateName.value = (payload.title || '').replace(/^Interview with\s*/i,'');
     }
   }

   // Simple timer
   let timerId = null, seconds = 0;
   const display = document.getElementById('timerDisplay');
   const startBtn = document.getElementById('startTimerBtn');
   const stopBtn = document.getElementById('stopTimerBtn');
   
   if (startBtn && display) {
     startBtn.addEventListener('click', () => {
       if (timerId) return;
       seconds = 0;
       timerId = setInterval(() => {
         seconds++;
         const h = String(Math.floor(seconds/3600)).padStart(2,'0');
         const m = String(Math.floor((seconds%3600)/60)).padStart(2,'0');
         const s = String(seconds%60).padStart(2,'0');
         display.textContent = `${h}:${m}:${s}`;
       }, 1000);
       startBtn.disabled = true;
       if (stopBtn) stopBtn.disabled = false;
     });
   }
   
   if (stopBtn) {
     stopBtn.addEventListener('click', () => {
       clearInterval(timerId); timerId=null;
       if (startBtn) startBtn.disabled = false;
       stopBtn.disabled = true;
     });
   }

   // Compute average rating
   function updateAverage(){
     const ratings = Array.from(document.querySelectorAll('select.rating')).map(s => s.selectedIndex+1);
     if (!ratings.length) return;
     const avg = ratings.reduce((a,b)=>a+b,0)/ratings.length;
     const avgRatingEl = document.getElementById('avgRating');
     if (avgRatingEl) avgRatingEl.textContent = avg.toFixed(2);
   }
   document.querySelectorAll('select.rating').forEach(s => s.addEventListener('change', updateAverage));
   updateAverage();

   // Persist as draft in localStorage
   const DRAFT_KEY = 'gambytes_conduct_draft';
   const saveDraftBtn = document.getElementById('saveDraft');
   if (saveDraftBtn) {
     saveDraftBtn.addEventListener('click', () => {
       const data = collectData();
       localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
       alert('Draft saved.');
     });
   }

   // Submit assessment
   assessmentForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = collectData();
  
    // Load old submissions
    const SUBMITTED_KEY = 'gambytes_submitted';
    const existing = JSON.parse(localStorage.getItem(SUBMITTED_KEY) || '[]');
    existing.push(data);
    localStorage.setItem(SUBMITTED_KEY, JSON.stringify(existing));
  
    // Remove this meeting from upcoming
    const SCHEDULE_KEY = 'gambytes_schedules';
    let schedules = JSON.parse(localStorage.getItem(SCHEDULE_KEY) || '[]');
    schedules = schedules.filter(m => m.title !== data.meeting.title);
    localStorage.setItem(SCHEDULE_KEY, JSON.stringify(schedules));
  
    alert('Assessment submitted successfully!');
    sessionStorage.removeItem('conduct_payload');
  
    // Redirect back to scheduling page
    window.location.href = 'scheduling.html';
  });
  

   function collectData(){
     const obj = {
       meeting: payload || {},
       personal: {
         candidateName: document.getElementById('candidateName')?.value || '',
         position: document.getElementById('position')?.value || '',
         currentCompany: document.getElementById('currentCompany')?.value || '',
         noticePeriod: document.getElementById('noticePeriod')?.value || '',
         familyBg: document.getElementById('familyBg')?.value || '',
         marital: document.getElementById('marital')?.value || '',
         kids: document.getElementById('kids')?.value || '',
         otherIncome: document.getElementById('otherIncome')?.value || '',
         conveyance: document.getElementById('conveyance')?.value || '',
         residence: document.getElementById('residence')?.value || '',
         locality: document.getElementById('locality')?.value || '',
       },
       ratings: Array.from(document.querySelectorAll('select.rating')).reduce((acc, s)=>{
         acc[s.dataset.name] = s.value; return acc;
       }, {}),
       notes: document.getElementById('notes')?.value || '',
       verdict: document.getElementById('verdict')?.value || '',
       duration: document.getElementById('timerDisplay')?.textContent || '00:00:00',
     };
     return obj;
   }
 })();

 
 
   // basic scheduling behavior: choose time slot, submit, and render list (persist in localStorage)
   (function initScheduling() {
     // Function to initialize scheduling
     function setupScheduling() {
       // Check if we're on the scheduling page
       const scheduleForm = document.getElementById('scheduleForm');
       if (!scheduleForm) return; // Exit if not on scheduling page
       
       console.log('Scheduling form found, initializing...');

       const timeButtons = document.querySelectorAll('.time-slot');
       const timeInput = document.getElementById('time');
       const scheduleList = document.getElementById('scheduleList');
       const emptyState = document.getElementById('emptyState');
       const STORAGE_KEY = 'gambytes_schedules';

       // Time slot button handlers
       timeButtons.forEach(btn => {
         btn.addEventListener('click', () => {
           timeButtons.forEach(b => b.classList.remove('active'));
           btn.classList.add('active');
           if (timeInput) timeInput.value = btn.dataset.time;
         });
       });

       function loadSchedules() {
         const raw = localStorage.getItem(STORAGE_KEY);
         return raw ? JSON.parse(raw) : [];
       }

       function saveSchedules(arr) {
         localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
       }

       function renderSchedules() {
         const items = loadSchedules().sort((a,b)=> new Date(a.date) - new Date(b.date));
         if (!scheduleList || !emptyState) {
           console.error('Schedule list or empty state element not found');
           return;
         }
         
         console.log('Rendering schedules:', items);
         
         scheduleList.innerHTML = '';
         if (!items.length) {
           emptyState.style.display = 'block';
           return;
         }
         emptyState.style.display = 'none';
         
         // Store items globally for button click handlers
         window._scheduleItems = items;
         
         items.forEach((it, idx) => {
           const li = document.createElement('li');
           li.className = 'list-group-item';
           li.innerHTML = `
             <div class="d-flex justify-content-between align-items-start">
               <div>
                 <div class="fw-bold">${escapeHtml(it.title)}</div>
                 <small class="text-muted">${escapeHtml(it.room)} • ${formatDate(it.date)} • ${escapeHtml(it.time)}</small>
                 ${it.notes ? `<div class="mt-1 small">${escapeHtml(it.notes)}</div>` : ''}
               </div>
               <div>
                 <button class="btn btn-sm btn-outline-primary conduct-btn" data-idx="${idx}">Conduct Interview</button>
               </div>
             </div>
           `;
           scheduleList.appendChild(li);
         });

         // attach action handlers (Conduct Interview -> go to conduct.html)
         scheduleList.querySelectorAll('button.conduct-btn').forEach(b => {
           b.addEventListener('click', e => {
             const idx = Number(e.currentTarget.dataset.idx);
             const meeting = window._scheduleItems[idx];
             if (meeting) {
               sessionStorage.setItem('conduct_payload', JSON.stringify({
                 title: meeting.title,
                 room: meeting.room,
                 date: formatDate(meeting.date),
                 time: meeting.time,
                 notes: meeting.notes || ''
               }));
               window.location.href = 'conduct.html';
             }
           });
         });
       }

       function formatDate(d) {
         const dt = new Date(d);
         return dt.toLocaleDateString(undefined, { weekday:'short', month:'short', day:'numeric', year:'numeric' });
       }

       function escapeHtml(s){ return s ? s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;') : ''; }

       scheduleForm.addEventListener('submit', function(e){
         e.preventDefault();
         const form = new FormData(this);
         const title = form.get('title')?.trim();
         const room = form.get('room');
         const date = form.get('date');
         const time = form.get('time');
         const notes = form.get('notes')?.trim();

         console.log('Form submitted:', { title, room, date, time, notes });

         if(!title || !room || !date || !time) {
           alert('Please fill meeting title, room, date and choose a time slot.');
           return;
         }

         const arr = loadSchedules();
         const newMeeting = { title, room, date, time, notes };
         arr.push(newMeeting);
         saveSchedules(arr);
         console.log('Meeting saved:', newMeeting);
         console.log('All schedules:', arr);
         renderSchedules();
         function renderSubmitted() {
          const submittedList = document.getElementById('submittedList');
          const submittedEmpty = document.getElementById('submittedEmpty');
          if (!submittedList) return;
        
          const SUBMITTED_KEY = 'gambytes_submitted';
          const submitted = JSON.parse(localStorage.getItem(SUBMITTED_KEY) || '[]');
        
          submittedList.innerHTML = '';
          if (!submitted.length) {
            submittedEmpty.style.display = 'block';
            return;
          }
          submittedEmpty.style.display = 'none';
        
          submitted.forEach(it => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML = `
              <div class="fw-bold">${escapeHtml(it.personal.candidateName || 'Unnamed Client')}</div>
              <small class="text-muted">${escapeHtml(it.meeting.room)} • ${escapeHtml(it.meeting.date)} • ${escapeHtml(it.meeting.time)}</small>
              <div class="small mt-1 text-success">✅ ${escapeHtml(it.verdict || 'Submitted')}</div>
            `;
            submittedList.appendChild(li);
          });
        }
         this.reset();
         timeButtons.forEach(b => b.classList.remove('active'));
         if (timeInput) timeInput.value = '';
         alert('Meeting scheduled successfully!');
       });

       // initialize
       console.log('Initializing scheduling page...');
       // set min date to today
       const dateInput = document.getElementById('date');
       if (dateInput) {
         const today = new Date().toISOString().split('T')[0];
         dateInput.setAttribute('min', today);
         console.log('Date input minimum set to:', today);
       }
       renderSchedules();
       console.log('Scheduling initialized successfully');
     }
     
     // Run if DOM is ready, otherwise wait for DOMContentLoaded
     if (document.readyState === 'loading') {
       document.addEventListener('DOMContentLoaded', setupScheduling);
     } else {
       setupScheduling();
     }
   })();
  