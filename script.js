let relativeCount = 0;

document.getElementById('Bdate').max = new Date().toISOString().split("T")[0];

function switchView(viewId) {
  document.querySelectorAll('.view-container').forEach(view => {
    view.classList.remove('active');
  });

  const targetView = document.getElementById(viewId);
  if (targetView) {
    targetView.classList.add('active');
  }

  if (viewId === 'view-login') {
    document.body.className = 'landing-bg';
  } else if (viewId === 'view-submitted') {
    document.body.className = 'success-bg';
  } else {
    document.body.className = 'form-bg'; 
  }
  
  window.scrollTo(0, 0);
}

// 1. Handle Registration
document.getElementById('registerForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('register.php', { method: 'POST', body: formData })
  .then(res => { if (!res.ok) return res.text().then(t => { throw new Error(t) }); return res.text(); })
  .then(data => {
    if(data.startsWith("SUCCESS:")) {
      const generatedID = data.split(":")[1];
      alert(`You can now log in.`);
      this.reset();
      document.getElementById('relatives-dynamic-container').innerHTML = '';
      relativeCount = 0;
      switchView('view-login');
    }
  })
  .catch(err => alert("Registration Error: " + err.message));
});

// 2. Handle Login
document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('login.php', { method: 'POST', body: formData })
  .then(res => { if (!res.ok) return res.text().then(t => { throw new Error(t) }); return res.text(); })
  .then(data => {
    if(data.startsWith("SUCCESS|")) {
      const patientID = data.split("|")[1];
      this.reset();
      document.getElementById('welcome-message').innerText = "Patient ID: " + patientID;
      switchView('view-landing');
    }
  })
  .catch(err => alert("Login Error: " + err.message));
});

// 3. Handle Form Submission
document.getElementById('assistanceRequestForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('connect.php', { method: 'POST', body: formData })
  .then(res => { if (!res.ok) return res.text().then(t => { throw new Error(t) }); return res.text(); })
  .then(data => {
    console.log("Server response:", data);

    if (data.trim() === "SUCCESS") {
        this.reset();
        document.getElementById('requestDetails').style.display = 'none';
        switchView('view-submitted');
    } else {
        alert(data);
    }
  })
  .catch(err => alert("Submission Error: " + err.message));
});

// 4. Logout User
function logoutUser() {
  fetch('logout.php')
  .then(() => {
    document.getElementById('assistanceRequestForm').reset();
    switchView('view-login');
  });
}

// UI Helpers
function addNewRelativeEntry() {
  relativeCount++;
  const container = document.getElementById('relatives-dynamic-container');
  const entryHtml = `
    <div class="relative-entry-card" id="relative-entry-${relativeCount}" style="background:#f8f9fa; padding:15px; margin-bottom:15px; border-radius:5px; border:1px solid #ddd;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <button type="button" class="btn btn-secondary" style="padding:5px 10px; font-size:12px;" onclick="removeRelativeEntry(${relativeCount})">✕ Remove</button>
      </div>
      <div class="form-group">
        <label for="relativeName">Full Name</label>
        <input type="text" class="form-control" id="relativeName" name="relativeName[]" required>
      </div>
      <div class="form-group">
        <label for="relativeAge">Age</label>
        <input type="number" class="form-control" id="relativeAge" name="relativeAge[]" min='18' required>
      </div>
      <div class="form-group">
        <label for="relativeCivStats">Civil Status</label>
        <select id="relativeCivStats" name="relCivilStatus[]" class="form-control" required>
          <option value="" disabled hidden selected>Select Status</option>
          <option value="Single">Single</option>
          <option value="Married">Married</option>
          <option value="Widow">Widow</option>
          <option value="Separated">Separated</option>
          <option value="Common-Law Partner">Common-Law Partner</option>
        </select>
      </div>
      <div class="form-group">
        <label for="relativeRelation-${relativeCount}">Relation To Patient</label>
        <input type="text" class="form-control" id="relativeRelation-${relativeCount}" name="relPatient[]" required>
      </div>
      <div class="form-group">
        <label for="relativeJob-${relativeCount}">Job</label>
        <input type="text" class="form-control" id="relativeJob-${relativeCount}" name="relJob[]" required>
      </div>
      <div class="form-group">
        <label for="relativeIncome-${relativeCount}">Monthly Income</label>
        <input type="number" class="form-control" id="relativeIncome-${relativeCount}" name="relIncome[]" required>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', entryHtml);
}

function removeRelativeEntry(id) {
  const target = document.getElementById(`relative-entry-${id}`);
  if (target) target.remove();
}

function toggleRequestDetails() {
    const requestType = document.getElementById('request').value;
    const detailsDiv = document.getElementById('requestDetails');
    if (['Others', 'Medicine', 'Laboratory/Diagnostic Procedures', 'Transplant'].includes(requestType)) {
        detailsDiv.style.display = 'block';
    } else {
        detailsDiv.style.display = 'none';
    }
}

