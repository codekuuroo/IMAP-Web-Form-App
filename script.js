/**
 * PCSO Medical Assistance Portal - Frontend Script
 */

let relativeCount = 0;

// Switches between the landing page, the form, and the success page
function switchView(viewId) {
  document.querySelectorAll('.view-container').forEach(view => {
    view.classList.remove('active');
  });

  const targetView = document.getElementById(viewId);
  if (targetView) {
    targetView.classList.add('active');
  }

  if (viewId === 'view-landing') {
    document.body.className = 'landing-bg';
  } else if (viewId === 'view-submitted') {
    document.body.className = 'success-bg';
  } else {
    document.body.className = 'form-bg'; 
  }
  
  window.scrollTo(0, 0);
}

// Generates and adds a new relative form block into the page
function addNewRelativeEntry() {
  relativeCount++;
  const container = document.getElementById('relatives-dynamic-container');
  
  const entryHtml = `
    <div class="relative-entry-card" id="relative-entry-${relativeCount}">
      <div class="entry-card-header">
        <h4>Relative Entry #${relativeCount}</h4>
        <button type="button" class="btn-remove-entry" onclick="removeRelativeEntry(${relativeCount})">✕ Remove</button>
      </div>
      <div class="form-group">
        <label>Full Name of Relative</label>
        <input type="text" class="form-control r-name" name="relativeName[]" placeholder="e.g. Juan E. Dela Cruz" required>
      </div>
      <div class="form-group">
        <label>Age</label>
        <input type="number" class="form-control r-age" name="relativeAge[]" min="18" placeholder="e.g. 35" required>
      </div>
      <div class="form-group">
        <label>Civil Status</label>
        <select class="form-control r-status" name="relCivStats[]" required>
          <option value="" disabled hidden selected>Select Civil Status</option>
          <option value="Single">Single</option>
          <option value="Widow">Widow</option>
          <option value="Married">Married</option>
          <option value="Separated">Separated</option>
          <option value="With Common Law Partner">With Common Law Partner</option>
        </select>
      </div>
      <div class="form-group">
        <label>Relation To Patient</label>
        <input type="text" class="form-control r-relation" name="relPatient[]" placeholder="e.g. Mother" required>
      </div>
      <div class="form-group">
        <label>Job</label>
        <input type="text" class="form-control r-job" name="relJob[]" placeholder="e.g. Call Center Agent" required>
      </div>
      <div class="form-group">
        <label>Monthly Income</label>
        <input type="number" class="form-control r-income" name="relIncome[]" min="0" placeholder="e.g. 25000" required>
      </div>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', entryHtml);
}

// Removes a specific relative block from the page
function removeRelativeEntry(id) {
  const target = document.getElementById(`relative-entry-${id}`);
  if (target) {
    target.remove();
  }
}

// Handles the main form submission to PHP
document.getElementById('masterApplicationForm').addEventListener('submit', function(e) {
  e.preventDefault(); 

  const formData = new FormData(this);

  fetch('connect.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
        return response.text().then(text => { throw new Error(text) });
    }
    return response.text();
  })
  .then(responseText => {
    console.log("Server Response:", responseText);
    switchView('view-submitted');
  })
  .catch(error => {
    console.error('Submission failed:', error.message);
    alert('An error occurred:\n' + error.message);
  });
});

// Toggles the "Other" input box for specific assistance types
function toggleRequestDetails() {
    const requestType = document.getElementById('request').value;
    const detailsDiv = document.getElementById('requestDetails');

    if (['others', 'medicine', 'laboratory', 'transplant'].includes(requestType)) {
        detailsDiv.style.display = 'block';
    } else {
        detailsDiv.style.display = 'none';
    }
}

// Wipes the form inputs and dynamic relative blocks to start a new entry
function resetPortal() {
  document.getElementById('masterApplicationForm').reset();
  document.getElementById('relatives-dynamic-container').innerHTML = '';
  relativeCount = 0;
  switchView('view-landing');
}

window.addEventListener('DOMContentLoaded', () => {
  switchView('view-landing');
});