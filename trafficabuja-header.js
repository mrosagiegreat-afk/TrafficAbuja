class TrafficAbujaHeader extends HTMLElement {
  connectedCallback() {
    if (this.dataset.rendered === "true") return;

    this.dataset.rendered = "true";

    if (!document.querySelector('link[data-trafficabuja-header-font="true"]')) {
      const preconnectFonts = document.createElement("link");
      preconnectFonts.rel = "preconnect";
      preconnectFonts.href = "https://fonts.googleapis.com";
      preconnectFonts.setAttribute("data-trafficabuja-header-font", "true");
      document.head.appendChild(preconnectFonts);

      const preconnectStatic = document.createElement("link");
      preconnectStatic.rel = "preconnect";
      preconnectStatic.href = "https://fonts.gstatic.com";
      preconnectStatic.crossOrigin = "anonymous";
      preconnectStatic.setAttribute("data-trafficabuja-header-font", "true");
      document.head.appendChild(preconnectStatic);

      const fontLink = document.createElement("link");
      fontLink.rel = "stylesheet";
      fontLink.href = "https://fonts.googleapis.com/css2?family=Pacifico&display=swap";
      fontLink.setAttribute("data-trafficabuja-header-font", "true");
      document.head.appendChild(fontLink);
    }

    const logoSrc = this.getAttribute("logo-src") || "logo.png";
    const brand = this.getAttribute("brand") || "trafficabuja";
    const pageTitle = this.getAttribute("page-title") || brand;
    const personalizeLabel = this.getAttribute("personalize-label") || "Personalize";
    const aboutLabel = this.getAttribute("about-label") || "FAQ";

    document.title = pageTitle;

    this.innerHTML = `
      <style>
        .trafficabuja-header-shell {
          width: 100%;
          padding: 1rem 4rem 0;
          position: relative;
          z-index: 1;
        }

        .trafficabuja-header-inner {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 2rem;
          padding: 0.5rem 0;
          margin: 0 auto 0.9rem;
        }

        .trafficabuja-header-logo {
          display: inline-flex;
          flex-direction: column;
          align-items: center;
          flex-shrink: 0;
          gap: 0.15rem;
        }

        .trafficabuja-header-logo img {
          display: block;
          width: auto;
          height: 44px;
          object-fit: contain;
        }

        .trafficabuja-header-wordmark {
          flex: 0 1 auto;
          text-align: left;
          font-family: "Pacifico", cursive;
          font-size: clamp(1.8rem, 4vw, 3.3rem);
          line-height: 1;
          color: #64748b;
          letter-spacing: 0.01em;
          white-space: nowrap;
          margin-right: auto;
        }

        .trafficabuja-header-nav {
          display: flex;
          align-items: center;
          gap: 0.9rem;
          flex-wrap: wrap;
          justify-content: flex-end;
          margin-left: auto;
        }

        .trafficabuja-header-action {
          width: auto;
          min-height: 40px;
          padding: 0.55rem 0.9rem;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: none;
          background: transparent;
          box-shadow: none;
          transition: color 0.2s ease, background-color 0.2s ease;
          color: #2c2c2c;
          cursor: pointer;
          border-radius: 999px;
          font: inherit;
          font-size: 0.92rem;
          font-weight: 500;
          letter-spacing: 0.01em;
          gap: 0.5rem;
          white-space: nowrap;
        }

        .trafficabuja-header-action:hover,
        .trafficabuja-header-action:focus-visible {
          background: rgba(15, 23, 42, 0.06);
          outline: none;
        }

        .trafficabuja-header-action--personalize:hover,
        .trafficabuja-header-action--personalize:focus-visible {
          color: #1da1f2;
        }

        .trafficabuja-header-action--about:hover,
        .trafficabuja-header-action--about:focus-visible {
          color: #5865f2;
        }

        .trafficabuja-header-icon {
          display: inline-flex;
          width: 18px;
          height: 18px;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }

        .trafficabuja-header-icon svg {
          width: 100%;
          height: 100%;
          display: block;
          fill: currentColor;
        }

        @media (max-width: 768px) {
          .trafficabuja-header-shell {
            padding: 0.85rem 2rem 0;
          }

          .trafficabuja-header-inner {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
          }

          .trafficabuja-header-wordmark {
            flex: 1 1 auto;
            text-align: center;
            font-size: clamp(1.2rem, 6vw, 2rem);
            margin-right: 0;
          }

          .trafficabuja-header-nav {
            gap: 0.6rem;
            width: auto;
            justify-content: flex-end;
            align-self: center;
          }

          .trafficabuja-header-action {
            min-height: 32px;
            width: 32px;
            min-width: 32px;
            padding: 0.35rem;
            gap: 0;
          }

          .trafficabuja-header-icon {
            width: 14px;
            height: 14px;
          }

          .trafficabuja-header-label {
            display: none;
          }

          .trafficabuja-header-action--about .trafficabuja-header-label {
            display: inline;
          }
        }
      </style>

      <div class="trafficabuja-header-shell">
        <div class="trafficabuja-header-inner">
          <div class="trafficabuja-header-logo">
            <img src="${logoSrc}" alt="trafficabuja logo">
          </div>
          <div class="trafficabuja-header-wordmark" aria-label="${brand} brand name">${brand}</div>
          <div class="trafficabuja-header-nav" aria-label="Header actions">
            <button class="trafficabuja-header-action trafficabuja-header-action--personalize" type="button" aria-label="${personalizeLabel}">
              <span class="trafficabuja-header-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                  <path d="M10 4a2 2 0 1 1 4 0v1.08a7.002 7.002 0 0 1 2.61 1.08l.77-.77a2 2 0 1 1 2.83 2.83l-.77.77A7.002 7.002 0 0 1 20 11h1a2 2 0 1 1 0 4h-1.08a7.002 7.002 0 0 1-1.08 2.61l.77.77a2 2 0 1 1-2.83 2.83l-.77-.77A7.002 7.002 0 0 1 13 21v1a2 2 0 1 1-4 0v-1.08a7.002 7.002 0 0 1-2.61-1.08l-.77.77a2 2 0 1 1-2.83-2.83l.77-.77A7.002 7.002 0 0 1 4 13H3a2 2 0 1 1 0-4h1.08a7.002 7.002 0 0 1 1.08-2.61l-.77-.77a2 2 0 1 1 2.83-2.83l.77.77A7.002 7.002 0 0 1 11 5.08V4Zm2 5a3 3 0 1 0 0 6a3 3 0 0 0 0-6Z"></path>
                </svg>
              </span>
              <span class="trafficabuja-header-label">${personalizeLabel}</span>
            </button>
            <button class="trafficabuja-header-action trafficabuja-header-action--about" type="button" aria-label="${aboutLabel}">
              <span class="trafficabuja-header-label">${aboutLabel}</span>
            </button>
          </div>
        </div>
      </div>
    `;
  }
}

customElements.define("trafficabuja-header", TrafficAbujaHeader);
