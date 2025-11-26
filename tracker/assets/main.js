// tiny helpers for showing modals + adding article URL inputs
document.addEventListener('click', function(e){
  if(e.target.matches('[data-modal-open]')) {
    document.querySelector(e.target.dataset.modalOpen).style.display='flex';
  }
  if(e.target.matches('[data-modal-close]') || e.target.matches('.modal')) {
    let m = e.target.closest('.modal') || document.querySelector(e.target.dataset.modalClose);
    if(m) m.style.display='none';
  }
});

function addArticleField(containerSelector){
  let container = document.querySelector(containerSelector);
  if(!container) return;
  let div = document.createElement('div');
  div.className = 'flex-row';
  div.innerHTML = `<input class="input" name="article_urls[]" placeholder="https://example.com/article"><button type="button" class="button small-ghost" onclick="this.parentNode.remove()">-</button>`;
  container.appendChild(div);
}