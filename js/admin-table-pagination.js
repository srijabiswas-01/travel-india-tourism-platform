document.querySelectorAll('.admin-content .panel').forEach((panel) => {
  const body = panel.querySelector(':scope > .table-wrap > table')?.tBodies[0];
  if (!body || panel.querySelector(':scope > .pagination')) return;
  const rows = [...body.rows];
  if (rows.length <= 10) return;
  let page = 1;
  const pager = document.createElement('nav');
  pager.className = 'pagination client-pagination';
  pager.setAttribute('aria-label', 'Table pages');
  panel.append(pager);
  function render() {
    const available = rows.filter(row => row.dataset.searchHidden !== 'true');
    const pages = Math.max(1, Math.ceil(available.length / 10));
    page = Math.min(page, pages);
    rows.forEach(row => { row.hidden = true; });
    available.slice((page - 1) * 10, page * 10).forEach(row => { row.hidden = false; });
    pager.replaceChildren();
    for (let number = 1; number <= pages; number++) {
      const link = document.createElement('a');
      link.href = '#'; link.textContent = number;
      if (number === page) link.className = 'active';
      link.addEventListener('click', event => { event.preventDefault(); page = number; render(); panel.scrollIntoView({ block: 'start' }); });
      pager.append(link);
    }
    pager.hidden = pages <= 1;
  }
  const search = panel.querySelector('.directory-tools input[type="search"]');
  if (search) search.addEventListener('input', () => {
    const term = search.value.trim().toLowerCase();
    rows.forEach(row => { row.dataset.searchHidden = String(term !== '' && !row.textContent.toLowerCase().includes(term)); });
    page = 1; render();
  });
  panel.querySelector('.directory-tools .reset-btn')?.addEventListener('click', () => setTimeout(() => { rows.forEach(row => { row.dataset.searchHidden = 'false'; }); page = 1; render(); }));
  render();
});
