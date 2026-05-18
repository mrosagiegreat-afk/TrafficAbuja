(function () {
  const root = document.getElementById('header-root');
  const currentScript = document.currentScript || document.scripts[document.scripts.length - 1];
  const headerUrl = currentScript ? new URL('../header.html', currentScript.src).href : 'header.html';
  const target = root || document.body;
  if (!target) return;

  fetch(headerUrl)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Failed to load ${headerUrl} (${response.status})`);
      }
      return response.text();
    })
    .then((html) => {
      if (root) {
        root.innerHTML = html;
      } else {
        document.body.insertAdjacentHTML('afterbegin', html);
      }

      const header = document.querySelector('.trafficabuja-header-shell');
      if (!header) return;

      const updateHeaderScroll = () => {
        header.classList.toggle('trafficabuja-header-shell--scrolled', window.scrollY > 50);
      };

      updateHeaderScroll();
      window.addEventListener('scroll', updateHeaderScroll, { passive: true });
    })
    .catch((error) => {
      console.error('Header load failed:', error);
    });
})();
