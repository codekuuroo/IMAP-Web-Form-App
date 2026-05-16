document.addEventListener('DOMContentLoaded', () => {
  const reasonSelect = document.getElementById('CivStats');
  const otherInputContainer = document.getElementById('otherContainer');

  reasonSelect.addEventListener('change', function() {
    if (this.value === 'other') {
      otherInputContainer.style.display = 'block';
    } else {
      otherInputContainer.style.display = 'none';
    }
  });
});