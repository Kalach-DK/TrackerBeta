</div> <!-- .tracker-app -->
<script src="/tracker/assets/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Open modal buttons
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = this.getAttribute('data-modal-open');
      document.querySelector(target)?.classList.add('active');
    });
  });

  // Close modal buttons
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', function() {
      this.closest('.modal')?.classList.remove('active');
    });
  });

  // Close modal when clicking background
  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) modal.classList.remove('active');
    });
  });
});

// Add new article URL field dynamically
function addArticleField(containerSelector) {
  const container = document.querySelector(containerSelector);
  const input = document.createElement('input');
  input.className = 'input';
  input.name = 'article_urls[]';
  input.placeholder = 'Article URL';
  container.appendChild(input);
}
</script>
</body>
</html>