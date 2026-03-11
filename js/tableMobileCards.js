(function () {
  function applyFilter(inputId, listId, emptyId, options) {
    const config = options || {};
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
    const empty = document.getElementById(emptyId);
    if (!list) return 0;

    const query = input ? input.value.trim().toLowerCase() : '';
    const cards = list.querySelectorAll('[data-search-text]');
    let visibleCount = 0;

    cards.forEach(card => {
      const haystack = (card.getAttribute('data-search-text') || '').toLowerCase();
      const visible = !query || haystack.includes(query);
      card.style.display = visible ? '' : 'none';
      if (visible) visibleCount += 1;
    });

    if (empty) {
      empty.style.display = visibleCount === 0 ? 'block' : 'none';
      empty.textContent = query
        ? (config.emptySearchText || 'No matching records found.')
        : (config.emptyText || 'No records available.');
    }

    return visibleCount;
  }

  function register(inputId, listId, emptyId, options) {
    const input = document.getElementById(inputId);
    if (input && !input.dataset.mobileCardsBound) {
      input.addEventListener('input', () => applyFilter(inputId, listId, emptyId, options));
      input.dataset.mobileCardsBound = 'true';
    }
    return applyFilter(inputId, listId, emptyId, options);
  }

  window.mobileTableCards = {
    applyFilter,
    register
  };
})();
